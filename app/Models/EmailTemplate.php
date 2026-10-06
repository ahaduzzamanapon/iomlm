<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'course_id',
        'batch_id',
        'gender',
        'subject',
        'content',
        'is_system',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'category_label',
        'gender_label',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'EXAM'      => 'পরীক্ষা সংক্রান্ত',
            'FEES'      => 'ফি ও একাউন্টস',
            'CLASS'     => 'ক্লাস ও রুটিন',
            'HOLIDAY'   => 'ছুটি সংক্রান্ত',
            'ADMISSION' => 'ভর্তি সংক্রান্ত',
            default     => 'সাধারণ বিজ্ঞপ্তি',
        };
    }

    public function getGenderLabelAttribute(): string
    {
        return match ($this->gender) {
            'Male'   => 'ভাইদের শাখা (পুরুষ)',
            'Female' => 'বোনদের শাখা (মহিলা)',
            default  => 'সকলের জন্য (উভয়)',
        };
    }

    /**
     * Resolve the best admission email template for a given course, batch, and gender.
     */
    public static function resolveAdmissionTemplate(?int $courseId, ?int $batchId, ?string $gender = null): ?self
    {
        $normalizedGender = 'All';
        if ($gender) {
            $g = strtolower(trim($gender));
            if ($g === 'male' || str_starts_with($g, 'm') || str_starts_with($g, 'প')) {
                $normalizedGender = 'Male';
            } elseif ($g === 'female' || str_starts_with($g, 'f') || str_starts_with($g, 'মহ')) {
                $normalizedGender = 'Female';
            }
        }

        $base = self::where('is_active', true)
            ->where(function ($q) {
                $q->where('category', 'ADMISSION')->orWhereNull('category');
            });

        // 1. Try exact course + batch + gender
        if ($courseId && $batchId && $normalizedGender !== 'All') {
            $tpl = (clone $base)->where('course_id', $courseId)
                ->where('batch_id', $batchId)
                ->where('gender', $normalizedGender)
                ->first();
            if ($tpl) return $tpl;
        }

        // 2. Try course + batch + 'All'
        if ($courseId && $batchId) {
            $tpl = (clone $base)->where('course_id', $courseId)
                ->where('batch_id', $batchId)
                ->where('gender', 'All')
                ->first();
            if ($tpl) return $tpl;
        }

        // 3. Try course + gender
        if ($courseId && $normalizedGender !== 'All') {
            $tpl = (clone $base)->where('course_id', $courseId)
                ->whereNull('batch_id')
                ->where('gender', $normalizedGender)
                ->first();
            if ($tpl) return $tpl;
        }

        // 4. Try course + 'All'
        if ($courseId) {
            $tpl = (clone $base)->where('course_id', $courseId)
                ->whereNull('batch_id')
                ->where('gender', 'All')
                ->first();
            if ($tpl) return $tpl;
        }

        // 5. Fallback: Any generic active admission template
        return (clone $base)->orderByDesc('is_system')->first();
    }

    /**
     * Compile template with placeholders.
     */
    public function compile(array $vars): array
    {
        $subject = $this->subject ?: '🎉 ইসলামিক অনলাইন মাদ্রাসায় আপনার ভর্তি নিশ্চিত হয়েছে';
        $content = $this->content;

        foreach ($vars as $key => $val) {
            $subject = str_replace($key, (string)$val, $subject);
            $content = str_replace($key, (string)$val, $content);
        }

        return [
            'subject' => $subject,
            'content' => $content,
        ];
    }

    /**
     * Seed initial standard templates if table is empty.
     */
    public static function seedDefaultTemplates(): void
    {
        if (self::count() > 0) {
            return;
        }

        $defaults = [
            [
                'name'      => 'পরীক্ষার সময়সূচী ও নির্দেশিকা',
                'category'  => 'EXAM',
                'subject'   => 'জরুরি নোটিশ: আসন্ন সেমিস্টার পরীক্ষার সময়সূচী ও নির্দেশিকা',
                'content'   => "আসসালামু আলাইকুম ওয়া রাহমাতুল্লাহ,\n\nসকল শিক্ষার্থীদের অবগতির জন্য জানানো যাচ্ছে যে, আগামী [তারিখ] থেকে সেমিস্টার পরীক্ষা শুরু হতে যাচ্ছে।\n\nগুরুত্বপূর্ণ নির্দেশনাবলী:\n১. নির্ধারিত সময়ের ১৫ মিনিট পূর্বে পরীক্ষার পোর্টালে লগইন করে প্রস্তুত থাকুন।\n২. আপনার স্টুডেন্ট আইডি ও পাসওয়ার্ড দিয়ে লগইন নিশ্চিত করুন।\n৩. পরীক্ষার রুটিন ও প্রশ্নপদ্ধতি স্টুডেন্ট পোর্টালের \"পরীক্ষা\" মেনুতে পাবেন।\n৪. কোনো প্রযুক্তিগত সমস্যার সম্মুখীন হলে অবিলম্বে হেল্পডেস্কে যোগাযোগ করুন।\n\nআল্লাহ তায়ালা আপনাদের পরীক্ষায় উত্তম সাফল্য দান করুন।\n\nবিনীত,\nপরীক্ষা নিয়ন্ত্রক শাখা,\nইসলামিক অনলাইন মাদ্রাসা (IOM)",
                'is_system' => true,
            ],
            [
                'name'      => 'সেমিস্টার ফি / বকেয়া ফি পরিশোধের তাগিদ',
                'category'  => 'FEES',
                'subject'   => 'তাগিদপত্র: সেমিস্টার ফি / বকেয়া ফি পরিশোধ সংক্রান্ত',
                'content'   => "আসসালামু আলাইকুম ওয়া রাহমাতুল্লাহ,\n\nসম্মানিত শিক্ষার্থী,\nআপনার অবগতির জন্য জানানো যাচ্ছে যে, চলতি সেমিস্টারের নির্ধারিত ফি পরিশোধের শেষ সময় আগামী [তারিখ]।\n\nআপনার স্টুডেন্ট পোর্টালে লগইন করে \"ফি ও পেমেন্ট\" মেনু থেকে অনলাইনেই সহজে বিকাশ/নগদ/রকেটের মাধ্যমে ফি পরিশোধ করতে পারেন। ফি পরিশোধে কোনো অসামঞ্জস্য থাকলে একাউন্টস শাখায় যোগাযোগ করার জন্য অনুরোধ করা হলো।\n\nজাযাকুমুল্লাহু খাইরান।\n\nধন্যবাদান্তে,\nহিসাব ও অর্থ বিভাগ,\nইসলামিক অনলাইন মাদ্রাসা (IOM)",
                'is_system' => true,
            ],
            [
                'name'      => 'নতুন সেমিস্টারের ক্লাস শুরু ও রুটিন',
                'category'  => 'CLASS',
                'subject'   => 'বিজ্ঞপ্তি: নতুন সেমিস্টারের নিয়মিত অনলাইন ক্লাস শুরু সংক্রান্ত',
                'content'   => "আসসালামু আলাইকুম ওয়া রাহমাতুল্লাহ,\n\nআইওএম-এর সম্মানিত সকল শিক্ষার্থীদের জানানো যাচ্ছে যে, আগামী [তারিখ], [বার] থেকে নতুন সেমিস্টারের নিয়মিত লাইভ ক্লাস শুরু হতে যাচ্ছে।\n\nশ্রেণিকক্ষে যোগদানের নিয়মাবলী:\n১. প্রতিদিনের লাইভ ক্লাসের লিংক ও সময়সূচী স্টুডেন্ট পোর্টালে \"আমার রুটিন ও ক্লাস\" সেকশনে পাবেন।\n২. যথাসময়ে ক্লাসে যুক্ত হওয়া প্রতিটি শিক্ষার্থীর জন্য বাধ্যতামূলক।\n৩. কোনো কারণে লাইভ ক্লাস মিস হলে \"ক্লাস রেকর্ড\" সেকশনে রেকর্ডেড ভিডিও পাওয়া যাবে।\n\nসকলের সার্বিক সাফল্য ও ইলমি অগ্রগতি কামনা করছি।\n\nশুভেচ্ছান্তে,\nএকাডেমিক কাউন্সিল,\nইসলামিক অনলাইন মাদ্রাসা (IOM)",
                'is_system' => true,
            ],
            [
                'name'      => 'বিশেষ ছুটি ও পাঠদান স্থগিতের নোটিশ',
                'category'  => 'HOLIDAY',
                'subject'   => 'নোটিশ: [ছুটির নাম / উপলক্ষ] উপলক্ষ্যে একাডেমিক কার্যক্রম বন্ধ সংক্রান্ত',
                'content'   => "আসসালামু আলাইকুম ওয়া রাহমাতুল্লাহ,\n\nসকল শিক্ষক ও শিক্ষার্থীদের অবগতির জন্য জানানো যাচ্ছে যে, [ছুটির নাম / উপলক্ষ] উপলক্ষ্যে আগামী [শুরুর তারিখ] হতে [শেষের তারিখ] পর্যন্ত মাদ্রাসার সকল প্রকার লাইভ ক্লাস ও অফিসিয়াল কার্যক্রম বন্ধ থাকবে।\n\nআগামী [পুনরায় শুরুর তারিখ], [বার] হতে যথারীতি রুটিন মাফিক সকল ক্লাস ও কার্যক্রম পুনরায় চালু হবে।\n\nছুটির দিনগুলোতে সকলকে নিয়মিত কুরআন তেলাওয়াত ও ব্যক্তিগত মুতালাআ অব্যাহত রাখার জন্য অনুরোধ করা হচ্ছে।\n\nধন্যবাদান্তে,\nপ্রশাসন বিভাগ,\nইসলামিক অনলাইন মাদ্রাসা (IOM)",
                'is_system' => true,
            ],
            [
                'name'      => 'ভর্তি নিশ্চিতকরণ ও স্বাগতম বার্তা (সাধারণ)',
                'category'  => 'ADMISSION',
                'subject'   => 'অভিনন্দন: {course} ({batch}) কোর্সে আপনার ভর্তি নিশ্চিত হয়েছে',
                'content'   => "আসসালামু আলাইকুম {name},\n\nআলহামদুলিল্লাহ! ইসলামিক অনলাইন মাদ্রাসায় \"{course}\" ({batch}) কোর্সে আপনার ভর্তি সফলভাবে অনুমোদিত ও নিশ্চিত হয়েছে। আপনাকে আইওএম পরিবারে আন্তরিক অভিনন্দন ও মোবারকবাদ!\n\nআপনার অফিসিয়াল লগইন তথ্য:\n----------------------------------------\n• স্টুডেন্ট আইডি: {student_id}\n• লগইন পাসওয়ার্ড: {password}\n• পোর্টাল লিংক: {login_url}\n----------------------------------------\n\nআপনি আপনার স্টুডেন্ট আইডি অথবা ইমেইল এবং পাসওয়ার্ড দিয়ে স্টুডেন্ট পোর্টালে লগইন করতে পারবেন। নিয়মিত লাইভ ক্লাসে অংশ নিন এবং পোর্টাল থেকে শিক্ষণ সামগ্রী সংগ্রহ করুন।\n\nআপনার ইলমি সফর সুন্দর ও বরকতময় হোক। আমীন।\n\nবিনীত,\nভর্তি শাখা,\nইসলামিক অনলাইন মাদ্রাসা (IOM)",
                'is_system' => true,
            ],
            [
                'name'      => 'তামরিন / অ্যাসাইনমেন্ট জমা দেওয়ার নোটিশ',
                'category'  => 'CLASS',
                'subject'   => 'নোটিশ: সংশ্লিষ্ট বিষয়ের তামরিন / অ্যাসাইনমেন্ট জমা সংক্রান্ত জরুরি নির্দেশনা',
                'content'   => "আসসালামু আলাইকুম ওয়া রাহমাতুল্লাহ,\n\nসকল শিক্ষার্থীদের দৃষ্টি আকর্ষণ করা যাচ্ছে যে, সংশ্লিষ্ট বিষয়ের তামরিন (অনুশীলনমূলক কাজ) আগামী [তারিখ] রাত ১১:৫৯ মিনিটের মধ্যে পোর্টালের মাধ্যমে জমা দিতে হবে।\n\nনির্দিষ্ট সময়ের পর কোনো তামরিন গ্রহণ করা হবে না এবং এটি চূড়ান্ত পরীক্ষার ফলাফলের সাথে যুক্ত হবে।\n\nসবার সার্বিক সফলতা কামনা করছি।\n\nএকাডেমিক বিভাগ,\nইসলামিক অনলাইন মাদ্রাসা (IOM)",
                'is_system' => true,
            ],
            [
                'name'      => 'পরীক্ষার ফলাফল প্রকাশ সংক্রান্ত বিজ্ঞপ্তি',
                'category'  => 'EXAM',
                'subject'   => 'বিজ্ঞপ্তি: সেমিস্টার পরীক্ষার ফলাফল প্রকাশিত হয়েছে',
                'content'   => "আসসালামু আলাইকুম ওয়া রাহমাতুল্লাহ,\n\nসকল শিক্ষার্থীদের অত্যন্ত আনন্দের সাথে জানানো যাচ্ছে যে, সম্প্রতি অনুষ্ঠিত সেমিস্টার পরীক্ষার ফলাফল প্রকাশিত হয়েছে।\n\nশিক্ষার্থীরা স্ব-স্ব স্টুডেন্ট পোর্টালে লগইন করে \"ফলাফল\" অপশন থেকে বিস্তারিত মার্কশিট ও গ্রেড দেখতে পারবেন। ফলাফলে কোনো আপত্তি থাকলে আগামী ৩ কার্যদিবসের মধ্যে আপিল করতে পারবেন।\n\nউত্তীর্ণ সকল শিক্ষার্থীদের আন্তরিক মোবারকবাদ!\n\nপরীক্ষা বিভাগ,\nইসলামিক অনলাইন মাদ্রাসা (IOM)",
                'is_system' => true,
            ],
            [
                'name'      => 'সাধারণ জরুরি ঘোষণা / বিজ্ঞপ্তি',
                'category'  => 'GENERAL',
                'subject'   => 'জরুরি ঘোষণা: ইসলামিক অনলাইন মাদ্রাসার সকল শিক্ষার্থীদের দৃষ্টি আকর্ষণ',
                'content'   => "আসসালামু আলাইকুম ওয়া রাহমাতুল্লাহ,\n\nআইওএম-এর সকল সম্মানিত শিক্ষক ও শিক্ষার্থীদের অবগতির জন্য জানানো যাচ্ছে যে,\n\n[এখানে আপনার বিজ্ঞপ্তির বিস্তারিত লিখুন]\n\nসবার সার্বিক সহযোগিতা একান্তভাবে কাম্য।\n\nজাযাকুমুল্লাহু খাইরান,\nকর্তৃপক্ষ,\nইসলামিক অনলাইন মাদ্রাসা (IOM)",
                'is_system' => true,
            ],
        ];

        foreach ($defaults as $d) {
            self::create($d);
        }
    }
}
