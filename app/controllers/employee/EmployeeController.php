<?php

namespace app\controllers\employee;

use app\controllers\Controller;
use app\models\AppliedLeave;
use app\models\LeaveType;
use app\models\User;
use app\Responses\View;

class EmployeeController extends Controller
{
    public function employeeProfile()
    {
        $email = $_SESSION['auth_user']['user_email'];
        $user = User::model()->where(['email'=> $email])->get()[0]; 
        $viewData = [
            'user' => $user,
        ];

        $headerTitle = 'Profile';
        return View::render('employee.profile', $viewData,  $headerTitle, $message = null, $messageCode = null, 200);
    }

    // public function updateProfile($request)
    // {
    //     if (isset($request['update_profile'])) {

    //         $userEmail = $_SESSION['auth_user']['user_email'];

    //         $user = User::model()->where(['email' => $userEmail])->first();

    //         if (!$user) {
    //             $_SESSION['message'] = "User not found";
    //             $_SESSION['message_code'] = "danger";
    //             header("Location: /profile");
    //             exit;
    //         }

    //         // Update fields
    //         $user->first_name = trim($request['first_name']);
    //         $user->last_name = trim($request['last_name']);
    //         $user->email = trim($request['email']);

    //         $response = $user->save();

    //         if ($response['status'] === 'success') {
    //             $_SESSION['message'] = "Profile updated successfully";
    //             $_SESSION['message_code'] = "success";
    //         } else {
    //             $_SESSION['message'] = $response['message'];
    //             $_SESSION['message_code'] = "danger";
    //         }

    //         header("Location: /employee/profile");
    //         exit;
    //     }
    // }

    public function updateProfile($request)
    {
        $email = $_SESSION['auth_user']['user_email'];

        $users = User::model()->where(['email' => $email])->get();

        if (!$users || count($users) === 0) {
            $_SESSION['message'] = "User not found";
            $_SESSION['message_code'] = "danger";
            header("Location: /employee/profile");
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

        header("Location: /employee/profile");
        exit;
    }

    public function employeeDepartment()
    {
        $email = $_SESSION['auth_user']['user_email'];
        $department = User::model()->where(['email' => $email])->get()[0]->department;   
        $viewData = [
            'department' => $department,
        ];

        $headerTitle = 'Department';
        return View::render('employee.department', $viewData,  $headerTitle, $message = null, $messageCode = null, 200);
    }

    public function employeeAppliedleaves()
    {
        $email = $_SESSION['auth_user']['user_email'];
        $user_id = User::model()->where(['email' => $email])->get('id')[0]->id; 
        $appliedleaves = AppliedLeave::model()->where(['applied_by' => $user_id])->get();

        $viewData = [
            'appliedleaves' => $appliedleaves,
        ];

        $headerTitle = 'Appliedleaves';
        if (isset($_SESSION['message']) && isset($_SESSION['message_code'])) {
            return View::render('employee.appliedleaves', $viewData,$headerTitle, $_SESSION['message'],  $_SESSION['message_code'], 200);
        }
        return View::render('employee.appliedleaves', $viewData,  $headerTitle, $message = null, $messageCode = null, 200);
    }

    public function employeeLeavetypes()
    {

        $leaveTypes = LeaveType::model()->all();

        $viewData = [
            'leaveTypes' => $leaveTypes,
        ];

        $headerTitle = 'Leave Types';
        return View::render('employee.leavetypes', $viewData, $headerTitle, null, null, 200);
    }


    public function leavetypes()
    {
        try {
            $perPage = isset($_GET['perPage']) ? (int) $_GET['perPage'] : 5;
            if (isset($_GET['search']) && $_GET['search'] === '') {
                $query = LeaveType::model()->paginate($perPage);
            } else {
                $query = LeaveType::model()->whereLike(['name' => $_GET['search']])->paginate($perPage);
            }

            $query['data'] = array_map(function ($entity) {
                return $entity->getAttributes();
            }, $query['data']);

            header('Content-Type: application/json');
            echo json_encode($query, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        } catch (\PDOException $e) {
            header('Content-Type: application/json', true, 500);
            echo json_encode(['error' => 'PDOException: ' . $e->getMessage()]);

        } catch (\Exception $e) {
            header('Content-Type: application/json', true, 500);
            echo json_encode(['error' => 'Exception: ' . $e->getMessage()]);

        } finally {
            exit;
        }
    }

    public function show($id)
    {
        $leavetype = LeaveType::model()->where(['id' => $id])->get();
        $idCheck = count($leavetype);

        if ($idCheck == 0) {
            View::redirect("/admin/leavetypes", "Leave Type not found", "warning", 302);
        }

        $headerTitle = 'Show Leave Type';
        $message = isset($_SESSION['message']) ? $_SESSION['message'] : null;
        $messageCode = isset($_SESSION['message_code']) ? $_SESSION['message_code'] : null;

        return View::render('employee.show', ['leavetype' => $leavetype], $headerTitle, $message, $messageCode, 200);
    }

    public function employeeApplyleaveForm()
    {
        $email = $_SESSION['auth_user']['user_email'];
        $user_id = User::model()->where(['email' => $email])->get('id')[0]->id; 
        $leave_type = LeaveType::model()->orderBy('created_at')->all();

        $viewData = [
            'leave_type' => $leave_type,
            'user_id' => $user_id,
        ];

        $headerTitle = 'Apply Leave Form';
        if (isset($_SESSION['message']) && isset($_SESSION['message_code'])) {
            return View::render('employee.applyleave', $viewData,$headerTitle, $_SESSION['message'],  $_SESSION['message_code'], 200);
        }
        return View::render('employee.applyleave', $viewData,  $headerTitle, $message = null, $messageCode = null, 200);
    }

    // public function employeeApplyleave($request)
    // {
    //     if (isset($request['apply_leave'])) {
    //         $leave = new AppliedLeave();
    //         $leave->applied_by = $this->parseInput($request['applied_by']);
    //         $leave->leavetype_id = $this->parseInput($request['leavetype_id']);
    //         $leave->description = $this->parseInput($request['description']);
    //         $leave->from_date = date('Y-m-d', strtotime($request['from_date']));
    //         $leave->to_date = date('Y-m-d', strtotime($request['to_date']));

    //         $response = $leave->save();
    //         if ($response['status'] === 'error') {
    //             View::redirect('/employee/appliedleaves', $response['message'], "danger", 302);
    //         } else if ($response['status'] === 'success') {
    //             View::redirect('/employee/appliedleaves', $response['message'], "success", 302);
    //         }
    //     }
    // }

    public function employeeApplyleave($request)
    {
        if (isset($request['apply_leave'])) {

            $leave = new AppliedLeave();
            $leave->applied_by   = $this->parseInput($request['applied_by']);
            $leave->leavetype_id = $this->parseInput($request['leavetype_id']);
            $leave->description  = $this->parseInput($request['description']);
            $leave->from_date    = date('Y-m-d', strtotime($request['from_date']));
            $leave->to_date      = date('Y-m-d', strtotime($request['to_date']));

            $response = $leave->save();

            if ($response['status'] === 'error') {
                return View::redirect('/employee/appliedleaves', $response['message'], "danger", 302);
            }

            if ($response['status'] === 'success') {

                // ✅ Get logged-in employee
                $employeeEmail = $_SESSION['auth_user']['user_email'];
                $employeeName  = $_SESSION['auth_user']['user_name'];

                // ✅ Get admins (assuming role = admin)
                $admins = \app\models\User::model()->where(['role_id' => 1])->get();

                // ✅ Send email to employee
                $this->sendLeaveEmail(
                    $employeeName,
                    $employeeEmail,
                    "Leave Application Submitted",
                    "Your leave from {$leave->from_date} to {$leave->to_date} has been submitted successfully."
                );

                // ✅ Send email to all admins
                if ($admins) {
                    foreach ($admins as $admin) {
                        $this->sendLeaveEmail(
                            $admin->first_name . ' ' . $admin->last_name,
                            $admin->email,
                            "New Leave Application",
                            "{$employeeName} applied for leave from {$leave->from_date} to {$leave->to_date}.",
                            "Login to https://leave.stawika.co.ke to check the status of the leave request."
                        );
                    }
                }

                return View::redirect('/employee/appliedleaves', "Leave applied successfully", "success", 302);
            }
        }
    }

    private function sendLeaveEmail($name, $email, $subject, $message)
    {
        try {
            $config = require (__DIR__ . '/../../../src/smtp.php');
            $smtp = $config['smtp'];

            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            $mail->isSMTP();
            $mail->SMTPAuth = true;

            $mail->Host = $smtp['host'];
            $mail->Username = $smtp['username'];
            $mail->Password = $smtp['password'];

            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($smtp['from'], "Leave Management System");
            $mail->addAddress($email, $name);

            $mail->isHTML(true);
            $mail->Subject = $subject;

            $mail->Body = "
                <h3>Hello $name,</h3>
                <p>$message</p>
                <br>
                <small>Leave Management System</small>
            ";

            $mail->send();

        } catch (\Exception $e) {
            error_log("Mail Error: " . $e->getMessage());
        }
    }

    public function employeeLogout($data)
    {
        if (isset($data['logout_btn']) && isset($_SESSION['auth_user']['user_email'])) {
            $this->destroySession();
            View::redirect("/", "Logged out successfully", "success", 302);
        }
    }

    private function parseInput($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}
