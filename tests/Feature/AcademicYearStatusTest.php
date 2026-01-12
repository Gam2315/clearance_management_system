<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\Clearance;
use App\Models\User;
use App\Models\Department;
use App\Models\Course;

class AcademicYearStatusTest extends TestCase
{
    use RefreshDatabase;

    protected $department;
    protected $course;
    protected $user;
    protected $academicYear;

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

        // Assert students are NOT archived - student data is always preserved
        $this->assertFalse($student1->fresh()->is_archived);
        $this->assertFalse($student2->fresh()->is_archived);

        // Assert clearances ARE archived when academic year becomes inactive
        $this->assertTrue($clearance1->fresh()->is_archived);
        $this->assertTrue($clearance2->fresh()->is_archived);

        // Assert academic year status is updated
        $this->assertEquals('inactive', $this->academicYear->fresh()->status);
    }

    /** @test */
    public function it_ensures_only_one_academic_year_is_active()
    {
        // Create another academic year that is currently active
        $otherAcademicYear = AcademicYear::create([
            'academic_year' => '2023-2024',
            'semester' => '2nd Semester',
            'status' => 'active'
        ]);

        // Set our test academic year to inactive first
        $this->academicYear->update(['status' => 'inactive']);

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

        // Assert our academic year is now active
        $this->assertEquals('active', $this->academicYear->fresh()->status);

        // Assert the other academic year is now inactive
        $this->assertEquals('inactive', $otherAcademicYear->fresh()->status);
    }

    /** @test */
    public function it_preserves_all_student_data_regardless_of_academic_year_status()
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

        // Assert the response is successful
        $response->assertRedirect(route('admin.academic_year.list_of_academic_year'));
        $response->assertSessionHas('success');

        // Assert both students remain unarchived - all data is preserved
        $this->assertFalse($studentInTargetYear->fresh()->is_archived);
        $this->assertFalse($studentInOtherYear->fresh()->is_archived);

        // Assert academic year status is updated
        $this->assertEquals('inactive', $this->academicYear->fresh()->status);
    }

    /** @test */
    public function it_can_get_active_academic_year_using_helper_method()
    {
        // Ensure our test academic year is active
        $this->academicYear->update(['status' => 'active']);

        // Test the helper method
        $activeYear = AcademicYear::getActive();

        $this->assertNotNull($activeYear);
        $this->assertEquals($this->academicYear->id, $activeYear->id);
        $this->assertTrue($activeYear->isActive());
    }
}
