<?php
namespace app\controllers\admin;

use app\controllers\Controller;
use app\models\AppliedLeave;
use app\models\Department;
use app\models\LeaveType;
use app\models\User;
use app\Responses\View;

class DashboardController extends Controller
{
    public function profile()
    {
        $email = $_SESSION['auth_user']['user_email'];
        $user = User::model()->where(['email' => $email])->get('first_name,last_name,email');
   
        $headerTitle = 'Admin Profile';
        return View::render('admin.profile', ['user' => $user], $headerTitle, $message = null, $messageCode = null, 200);
    }

    // public function updateProfile($request)
    // {
    //     // Check form submission
    //     // if (!isset($_POST['update_profile'])) {
    //     //     $_SESSION['message'] = "Invalid request";
    //     //     $_SESSION['message_code'] = "danger";
    //     //     header("Location: /admin/profile");
    //     //     exit;
    //     // }

    //     // // Get logged-in admin email
    //     // $sessionEmail = $_SESSION['auth_user']['user_email'];

    //     // // Fetch user (your ORM uses get())
    //     // $users = \app\models\User::model()->where(['email' => $sessionEmail])->get();

    //     // if (!$users || count($users) === 0) {
    //     //     $_SESSION['message'] = "Admin not found";
    //     //     $_SESSION['message_code'] = "danger";
    //     //     header("Location: /admin/profile");
    //     //     exit;
    //     // }

    //     $user = $users[0];

    //     // die($user);

    //     // Get form inputs (DO NOT over-sanitize)
    //     $first_name = trim($_POST['first_name'] ?? '');
    //     $last_name  = trim($_POST['last_name'] ?? '');
    //     $email      = trim($_POST['email'] ?? '');

    //     // Basic validation
    //     // if (empty($first_name) || empty($last_name) || empty($email)) {
    //     //     $_SESSION['message'] = "All fields are required";
    //     //     $_SESSION['message_code'] = "danger";
    //     //     header("Location: /admin/profile");
    //     //     exit;
    //     // }

    //     // Optional: check if email already exists (exclude current user)
    //     // $existing = \app\models\User::model()->where(['email' => $email])->get();
    //     // if ($existing && $existing[0]->id != $user->id) {
    //     //     $_SESSION['message'] = "Email already taken";
    //     //     $_SESSION['message_code'] = "danger";
    //     //     header("Location: /admin/profile");
    //     //     exit;
    //     // }

    //     // Update fields
    //     $user->first_name = $first_name;
    //     $user->last_name  = $last_name;
    //     $user->email      = $email;

    //     // Save
    //     $response = $user->save();

    //     if ($response['status'] === 'success') {

    //         // Update session
    //         $_SESSION['auth_user']['user_name'] =
    //             $user->first_name . ' ' . $user->last_name;

    //         $_SESSION['auth_user']['user_email'] = $user->email;

    //         $_SESSION['message'] = "Profile updated successfully";
    //         $_SESSION['message_code'] = "success";

    //     } else {
    //         $_SESSION['message'] = $response['message'] ?? "Update failed";
    //         $_SESSION['message_code'] = "danger";
    //     }

    //     header("Location: /admin/profile");
    //     exit;
    // }

    public function updateProfile($request)
    {
        $email = $_SESSION['auth_user']['user_email'];

        $users = User::model()->where(['email' => $email])->get();

        if (!$users || count($users) === 0) {
            $_SESSION['message'] = "User not found";
            $_SESSION['message_code'] = "danger";
            header("Location: /admin/profile");
            exit;
        }

        $user = $users[0];

        $user->first_name = $_POST['first_name'] ?? '';
        $user->last_name  = $_POST['last_name'] ?? '';
        $user->email      = $_POST['email'] ?? '';

        $response = $user->save();

        if ($response['status'] === 'success') {

            $_SESSION['auth_user']['user_name'] =
                $user->first_name . ' ' . $user->last_name;

            $_SESSION['auth_user']['user_email'] = $user->email;

            $_SESSION['message'] = "Profile updated successfully";
            $_SESSION['message_code'] = "success";

        } else {
            $_SESSION['message'] = $response['message'];
            $_SESSION['message_code'] = "danger";
        }

        header("Location: /admin/profile");
        exit;
    }

    // Method to handle the admin dashboard action
    public function dashboard()
    {
        // $employees = User::model()->get('gender,department_id,status,role_id');
        // $employees = array_map(function ($employee) {

        //     $data = [
        //         'gender' => $employee->getAttributes()['gender'],
        //         'status' => $employee->getAttributes()['status'],
        //     ];

        //     if (method_exists($employee, 'role')) {
        //         $role = $employee->role()->getAttributes();
        //         $data['role'] = $role['name'];
        //     }

        //     if (method_exists($employee, 'department')) {
        //         $department = $employee->department()->getAttributes();
        //         $data['department'] = $department['name'];
        //     }

        //     return $data;
        // }, $employees);

        $employees = User::model()->count();

        $departments = Department::model()->count();

        $apply_leave = AppliedLeave::model()->count();

        $leave_type = LeaveType::model()->count();

        $viewData = [
            'employees' => $employees,
            'departments' => $departments,
            'apply_leave' => $apply_leave,
            'leave_type' => $leave_type,
        ];

        $headerTitle = 'Admin Dashboard';
        $message = isset($_SESSION['message']) ? $_SESSION['message'] : null;
        $messageCode = isset($_SESSION['message_code']) ? $_SESSION['message_code'] : null;

        return View::render('admin.dashboard', $viewData, $headerTitle, $message, $messageCode, 200);
    }

    public function logout()
    {
        if (isset($_SESSION['auth_user']['user_email'])) {
            $this->destroySession();
            echo json_encode(['status' => 'success', 'message' => 'Logged out successfully', 'redirect' => '/login']);
            exit;
            // View::redirect("/", "Logged out successfully", "success", 302);
        }
    }
}
