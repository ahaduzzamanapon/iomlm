<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\Subject;
use App\Models\SubjectModule;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SubjectModuleFolderActionsTest extends TestCase
{
    use DatabaseTransactions;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::where('role', 'admin')->orWhere('role', 'super_admin')->first();
        if (!$this->adminUser) {
            $this->adminUser = User::create([
                'name'     => 'Test Admin',
                'email'    => 'admin_' . uniqid() . '@example.com',
                'password' => bcrypt('secret'),
                'role'     => 'admin',
            ]);
        }
    }

    /**
     * Test renaming a module folder updates all modules under that folder.
     */
    public function test_rename_folder_updates_all_modules_under_that_folder(): void
    {
        $subject = Subject::first();
        if (!$subject) {
            $subject = Subject::create([
                'code' => 'SUB_' . rand(100, 999),
                'name' => 'Test Subject for Folders',
            ]);
        }

        $folderOld = 'Old Folder ' . rand(100, 999);
        $folderNew = 'New Folder ' . rand(100, 999);

        $mod1 = SubjectModule::create([
            'subject_id'  => $subject->id,
            'title'       => 'Module 1',
            'folder_name' => $folderOld,
            'sequence_no' => 1,
        ]);

        $mod2 = SubjectModule::create([
            'subject_id'  => $subject->id,
            'title'       => 'Module 2',
            'folder_name' => $folderOld,
            'sequence_no' => 2,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.subjects.folders.rename', $subject), [
                'old_folder_name' => $folderOld,
                'new_folder_name' => $folderNew,
            ]);

        $response->assertSessionHas('success');

        $this->assertSame($folderNew, $mod1->fresh()->folder_name);
        $this->assertSame($folderNew, $mod2->fresh()->folder_name);
    }

    /**
     * Test cloning a folder replicates all modules under a new folder name with unique sequence numbers.
     */
    public function test_clone_folder_replicates_all_modules_under_new_folder(): void
    {
        $subject = Subject::first();
        if (!$subject) {
            $subject = Subject::create([
                'code' => 'SUB_' . rand(100, 999),
                'name' => 'Test Subject for Folders',
            ]);
        }

        $folderSource = 'Source Folder ' . rand(100, 999);
        $folderClone  = 'Cloned Folder ' . rand(100, 999);

        $mod1 = SubjectModule::create([
            'subject_id'  => $subject->id,
            'title'       => 'Syllabus A',
            'folder_name' => $folderSource,
            'sequence_no' => 10,
        ]);

        $mod2 = SubjectModule::create([
            'subject_id'  => $subject->id,
            'title'       => 'Syllabus B',
            'folder_name' => $folderSource,
            'sequence_no' => 11,
        ]);

        $initialCount = SubjectModule::where('subject_id', $subject->id)->count();

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.subjects.folders.clone', $subject), [
                'source_folder_name' => $folderSource,
                'new_folder_name'    => $folderClone,
            ]);

        $response->assertSessionHas('success');

        $clonedModules = SubjectModule::where('subject_id', $subject->id)
            ->where('folder_name', $folderClone)
            ->orderBy('sequence_no')
            ->get();

        $this->assertCount(2, $clonedModules);
        $this->assertSame('Syllabus A', $clonedModules[0]->title);
        $this->assertSame('Syllabus B', $clonedModules[1]->title);
        $this->assertGreaterThan(11, $clonedModules[0]->sequence_no);
        $this->assertGreaterThan($clonedModules[0]->sequence_no, $clonedModules[1]->sequence_no);
    }
}

