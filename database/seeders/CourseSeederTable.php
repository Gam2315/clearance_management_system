<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CourseSeederTable extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $courses = [
            
                ['course_name' => 'BS in Information Technology', 'course_code' => 'BSIT', 'department_id' => 1],
                ['course_name' => 'BS in Civil Engineering', 'course_code' => 'BSCE', 'department_id' => 1],
                ['course_name' => 'BS in Environmental and Sanitary Engineering', 'course_code' => 'BSEnSE', 'department_id' => 1],
                ['course_name' => 'BS in Computer Engineering', 'course_code' => 'BSCpE', 'department_id' => 1],
                ['course_name' => 'Bachelor of Library and Information Sciences', 'course_code' => 'BLIS', 'department_id' => 1],

                ['course_name' => 'BA in English Language Studies', 'course_code' => 'BAELS', 'department_id' => 2],
                ['course_name' => 'BS in Psychology', 'course_code' => 'BSPsych', 'department_id' => 2],
                ['course_name' => 'BS in Biology', 'course_code' => 'BSBio', 'department_id' => 2],
                ['course_name' => 'BS in Social Work', 'course_code' => 'BSSW', 'department_id' => 2],
                ['course_name' => 'BS in Public Administration', 'course_code' => 'BSPA', 'department_id' => 2],
                ['course_name' => 'BS in Biology Major in Microbiology', 'course_code' => 'BSBio-MicroBiology', 'department_id' => 2],
                ['course_name' => 'Bachelor in Secondary Education', 'course_code' => 'BSE', 'department_id' => 2],
                ['course_name' => 'Bachelor in Elementary Education', 'course_code' => 'BEE', 'department_id' => 2],
                ['course_name' => 'BS in Physical Education', 'course_code' => 'BPEd', 'department_id' => 2],
            
                ['course_name' => 'BS in Nursing', 'course_code' => 'BSN', 'department_id' => 3],
                ['course_name' => 'BS in Pharmacy', 'course_code' => 'BSPharma', 'department_id' => 3],
                ['course_name' => 'BS in Medical Technology', 'course_code' => 'BSMT', 'department_id' => 3],
                ['course_name' => 'BS in Physical Therapy', 'course_code' => 'BSPT', 'department_id' => 3],
                ['course_name' => 'BS in Radiologic Technology', 'course_code' => 'BSRT', 'department_id' => 3],
                ['course_name' => 'BS in Midwifery', 'course_code' => 'BSMidwifery', 'department_id' => 3],
            
                ['course_name' => 'BS in Accountancy', 'course_code' => 'BSA', 'department_id' => 4],
                ['course_name' => 'BS in Entrepreneurship', 'course_code' => 'BSEntrep', 'department_id' => 4],
                ['course_name' => 'BS in Business Administration major in Marketing Management', 'course_code' => 'BSBA-MM', 'department_id' => 4],
                ['course_name' => 'BS in Business Administration major in Financial Management', 'course_code' => 'BSBA-FM', 'department_id' => 4],
                ['course_name' => 'BS in Business Administration major in Operations Management', 'course_code' => 'BSBA-OM', 'department_id' => 4],
                ['course_name' => 'BS in Management Accounting', 'course_code' => 'BSMA', 'department_id' => 4],
                ['course_name' => 'BS in Hospitality Management', 'course_code' => 'BSHM', 'department_id' => 4],
                ['course_name' => 'BS in Tourism Management', 'course_code' => 'BSTM', 'department_id' => 4],
                ['course_name' => 'BS in Product Design and Marketing Innovation', 'course_code' => 'BSPDMI', 'department_id' => 4],
            
               

            

        ];
        Course::insert($courses);
    }
}
