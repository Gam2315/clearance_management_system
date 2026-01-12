<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\User;
use App\Models\Department;
use App\Models\Course;
use App\Models\Clearance;

class AcademicYearAutoAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $department;
    protected $course;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->department = Department::factory()->create();
        $this->course = Course::factory()->create(['department_id' => $this->department->id]);
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    /** @test */
    public function it_automatically_assigns_students_when_creating_active_academic_year()
    {
        // Create some test students
        $student1 = Student::factory()->create([
            'department_id' => $this->department->id,
            'course_id' => $this->course->id,
            'is_archived' => false,
            'academic_id' => null // No academic year assigned yet
        ]);
        
        $student2 = Student::factory()->create([
            'department_id' => $this->department->id,
            'course_id' => $this->course->id,
            'is_archived' => false,
            'academic_id' => null
        ]);

        // Create a new academic year with active status
        $response = $this->actingAs($this->user)
            ->post(route('admin.academic_year.store_new_academic_year'), [
                'academic_year' => '2024-2025',
                'semester' => '1st Semester',
                'status' => 'active'
            ]);

        // Assert the response is successful
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Get the created academic year
        $academicYear = AcademicYear::where('academic_year', '2024-2025')->first();
        $this->assertNotNull($academicYear);
        $this->assertEquals('active', $academicYear->status);

        // Assert students are automatically assigned to the new academic year
        $this->assertEquals($academicYear->id, $student1->fresh()->academic_id);
        $this->assertEquals($academicYear->id, $student2->fresh()->academic_id);

        // Assert clearances are created for the students
        $this->assertTrue(Clearance::where('student_id', $student1->id)
            ->where('academic_id', $academicYear->id)
            ->exists());
        $this->assertTrue(Clearance::where('student_id', $student2->id)
            ->where('academic_id', $academicYear->id)
            ->exists());
    }

    /** @test */
    public function it_does_not_assign_students_when_creating_inactive_academic_year()
    {
        // Create a test student
        $student = Student::factory()->create([
            'department_id' => $this->department->id,
            'course_id' => $this->course->id,
            'is_archived' => false,
            'academic_id' => null
        ]);

        $originalAcademicId = $student->academic_id;

        // Create a new academic year with inactive status
        $response = $this->actingAs($this->user)
            ->post(route('admin.academic_year.store_new_academic_year'), [
                'academic_year' => '2024-2025',
                'semester' => '1st Semester',
                'status' => 'inactive'
            ]);

        // Assert the response is successful
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Get the created academic year
        $academicYear = AcademicYear::where('academic_year', '2024-2025')->first();
        $this->assertNotNull($academicYear);
        $this->assertEquals('inactive', $academicYear->status);

        // Assert student academic_id remains unchanged
        $this->assertEquals($originalAcademicId, $student->fresh()->academic_id);

        // Assert no clearances are created
        $this->assertFalse(Clearance::where('student_id', $student->id)
            ->where('academic_id', $academicYear->id)
            ->exists());
    }

    /** @test */
    public function it_sets_other_academic_years_to_inactive_when_creating_active_year()
    {
        // Create an existing active academic year
        $existingAcademicYear = AcademicYear::create([
            'academic_year' => '2023-2024',
            'semester' => '2nd Semester',
            'status' => 'active'
        ]);

        // Create a new active academic year
        $response = $this->actingAs($this->user)
            ->post(route('admin.academic_year.store_new_academic_year'), [
                'academic_year' => '2024-2025',
                'semester' => '1st Semester',
                'status' => 'active'
            ]);

        // Assert the response is successful
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert the existing academic year is now inactive
        $this->assertEquals('inactive', $existingAcademicYear->fresh()->status);

        // Assert the new academic year is active
        $newAcademicYear = AcademicYear::where('academic_year', '2024-2025')->first();
        $this->assertEquals('active', $newAcademicYear->status);
    }

    /** @test */
    public function it_does_not_assign_archived_students()
    {
        // Create an archived student
        $archivedStudent = Student::factory()->create([
            'department_id' => $this->department->id,
            'course_id' => $this->course->id,
            'is_archived' => true,
            'academic_id' => null
        ]);

        // Create an active student
        $activeStudent = Student::factory()->create([
            'department_id' => $this->department->id,
            'course_id' => $this->course->id,
            'is_archived' => false,
            'academic_id' => null
        ]);

        // Create a new active academic year
        $response = $this->actingAs($this->user)
            ->post(route('admin.academic_year.store_new_academic_year'), [
                'academic_year' => '2024-2025',
                'semester' => '1st Semester',
                'status' => 'active'
            ]);

        $academicYear = AcademicYear::where('academic_year', '2024-2025')->first();

        // Assert only the active student is assigned
        $this->assertNull($archivedStudent->fresh()->academic_id);
        $this->assertEquals($academicYear->id, $activeStudent->fresh()->academic_id);
    }
}
