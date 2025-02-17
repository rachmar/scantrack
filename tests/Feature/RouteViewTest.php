<?php

namespace Tests\Feature;

use Tests\TestCase;

class RouteTest extends TestCase
{
    public function test_welcome_redirects_to_scan_index()
    {
        // Test if the welcome route redirects to the scan index route
        $response = $this->get('/');
        $response->assertRedirect(route('public.scan.index'));
    }

    public function test_scan_index_route_is_accessible()
    {
        // Test if the scan index route is accessible
        $response = $this->get(route('public.scan.index'));
        $response->assertStatus(200); // Expecting status 200 OK
    }

    public function test_visitor_index_route_is_accessible()
    {
        // Test if the visitor index route is accessible
        $response = $this->get(route('public.visitor.index'));
        $response->assertStatus(200); // Expecting status 200 OK
    }

   

    public function test_admin_dashboard_accessible_for_admin_only()
    {
        // Test if the admin dashboard is accessible for admins only
        // Here, we are testing the route directly without authentication
        $response = $this->get(route('admin'));
        $response->assertStatus(302); // Expecting redirect due to authentication middleware
    }

    public function test_reports_courses_index_accessible_for_admin()
    {
        // Test if the reports courses index route is accessible for admins only
        $response = $this->get(route('reports.courses.index'));
        $response->assertStatus(302); // Expecting redirect due to authentication middleware
    }

    public function test_reports_students_index_accessible_for_admin()
    {
        // Test if the reports students index route is accessible for admins only
        $response = $this->get(route('reports.students.index'));
        $response->assertStatus(302); // Expecting redirect due to authentication middleware
    }

    public function test_reports_visitors_index_accessible_for_admin()
    {
        // Test if the reports visitors index route is accessible for admins only
        $response = $this->get(route('reports.visitor.index'));
        $response->assertStatus(302); // Expecting redirect due to authentication middleware
    }


    public function test_show_student_route()
    {
        // Test if the show student route is accessible
        // Assume you have a student ID to pass
        $studentId = 1; // Adjust with actual ID if needed
        $response = $this->get(route('students.show', $studentId));
        $response->assertStatus(302); // Expecting redirect due to authentication middleware
    }
}
