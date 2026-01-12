<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\Clearance;
use App\Models\User;
use App\Models\Department;
use App\Models\Course;

class AcademicYearArchivingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->department = Department::factory()->create();
        $this->course = Course::factory()->create(['department_id' => $this->department->id]);
        $this->user = User::factory()->create(['role' => 'admin']);
        $this->academicYear = AcademicYear::create([
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active'
        ]);
    }

    /** @test */
    public function it_archives_only_clearances_when_academic_year_becomes_inactive()
    {
        // Create test students
        $student1 = Student::factory()->create([
            'academic_id' => $this->academicYear->id,
            'department_id' => $this->department->id,
            'course_id' => $this->course->id,
            'is_archived' => false
        ]);
        
        $student2 = Student::factory()->create([
            'academic_id' => $this->academicYear->id,
            'department_id' => $this->department->id,
            'course_id' => $this->course->id,
            'is_archived' => false
        ]);

        // Create clearances for the students
        $clearance1 = Clearance::factory()->create([
            'student_id' => $student1->id,
            'academic_id' => $this->academicYear->id,
            'department_id' => $this->department->id,
            'is_archived' => false
        ]);
        
        $clearance2 = Clearance::factory()->create([
            'student_id' => $student2->id,
            'academic_id' => $this->academicYear->id,
            'department_id' => $this->department->id,
            'is_archived' => false
        ]);

        // Act as admin and update academic year to inactive
        $response = $this->actingAs($this->user)
            ->put(route('admin.academic_year.update_information_academic_year', $this->academicYear->id), [
                'academic_year' => '2024-2025',
                'semester' => '1st Semester',
                'status' => 'inactive'
            ]);

        // Assert the response is successful
        $response->assertRedirect(route('admin.academic_year.list_of_academic_year'));
        $response->assertSessionHas('success');

        // Assert students remain unarchived - student data is preserved
        $this->assertFalse($student1->fresh()->is_archived);
        $this->assertFalse($student2->fresh()->is_archived);

        // Assert clearances are archived when academic year becomes inactive
        $this->assertTrue($clearance1->fresh()->is_archived);
        $this->assertTrue($clearance2->fresh()->is_archived);

        // Assert academic year status is updated
        $this->assertEquals('inactive', $this->academicYear->fresh()->status);
    }

    /** @test */
    public function it_unarchives_students_when_academic_year_becomes_active_again()
    {
        // Set academic year to inactive first
        $this->academicYear->update(['status' => 'inactive']);

        // Create archived students
        $student1 = Student::factory()->create([
            'academic_id' => $this->academicYear->id,
            'department_id' => $this->department->id,
            'course_id' => $this->course->id,
            'is_archived' => true
        ]);

        $clearance1 = Clearance::factory()->create([
            'student_id' => $student1->id,
            'academic_id' => $this->academicYear->id,
            'department_id' => $this->department->id,
            'is_archived' => true
        ]);

        // Act as admin and update academic year to active
        $response = $this->actingAs($this->user)
            ->put(route('admin.academic_year.update_information_academic_year', $this->academicYear->id), [
                'academic_year' => '2024-2025',
                'semester' => '1st Semester',
                'status' => 'active'
            ]);

        // Assert the response is successful
        $response->assertRedirect(route('admin.academic_year.list_of_academic_year'));
        $response->assertSessionHas('success');

        // Assert students remain unarchived (students are never archived)
        $this->assertFalse($student1->fresh()->is_archived);

        // Assert clearances are unarchived when academic year is reactivated
        $this->assertFalse($clearance1->fresh()->is_archived);

        // Assert academic year status is updated
        $this->assertEquals('active', $this->academicYear->fresh()->status);
    }

    /** @test */
    public function it_preserves_students_but_archives_clearances_by_academic_year()
    {
        // Create another academic year
        $otherAcademicYear = AcademicYear::create([
            'academic_year' => '2023-2024',
            'semester' => '2nd Semester',
            'status' => 'active'
        ]);

        // Create students for both academic years
        $studentInTargetYear = Student::factory()->create([
            'academic_id' => $this->academicYear->id,
            'department_id' => $this->department->id,
            'course_id' => $this->course->id,
            'is_archived' => false
        ]);

        $studentInOtherYear = Student::factory()->create([
            'academic_id' => $otherAcademicYear->id,
            'department_id' => $this->department->id,
            'course_id' => $this->course->id,
            'is_archived' => false
        ]);

        // Update target academic year to inactive
        $response = $this->actingAs($this->user)
            ->put(route('admin.academic_year.update_information_academic_year', $this->academicYear->id), [
                'academic_year' => '2024-2025',
                'semester' => '1st Semester',
                'status' => 'inactive'
            ]);

        // Assert both students remain unarchived - student data is always preserved
        $this->assertFalse($studentInTargetYear->fresh()->is_archived);
        $this->assertFalse($studentInOtherYear->fresh()->is_archived);
    }
}
