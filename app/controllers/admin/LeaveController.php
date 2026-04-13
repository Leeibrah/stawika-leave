<?php

namespace app\controllers\admin;

use app\Responses\View;
use app\models\AppliedLeave;
use app\controllers\Controller;

class LeaveController extends Controller
{

    public function index()
    {
        $headerTitle = 'Applied Leaves';
        $message = isset($_SESSION['message']) ? $_SESSION['message'] : null;
        $messageCode = isset($_SESSION['message_code']) ? $_SESSION['message_code'] : null;

        return View::render('admin.appliedleaves.index', null, $headerTitle, $message, $messageCode, 200);
    }

    public function appliedLeaves()
    {
        try {
            $itemsPerPage = isset($_GET['perPage']) ? (int) $_GET['perPage'] : 10;
            if (isset($_GET['search']) && $_GET['search'] === '') {
                $query = AppliedLeave::model()->paginate($itemsPerPage);
            } else {
                $query = AppliedLeave::model()->whereLike(['description' => $_GET['search']])->paginate($itemsPerPage);
            }

            $query['data'] = array_map(function ($leave) {

                // $data = $leave->getAttributes();
                $data = [
                    'id' => $leave->getAttributes()['id'],
                    'description' => $leave->getAttributes()['description'],
                    'status' => $leave->getAttributes()['status'],
                ];

                if (method_exists($leave, 'leavetype')) {
                    $leavetype = $leave->leavetype()->getAttributes();
                    $data['leavetype'] = [
                        'name' => $leavetype['name'],
                    ];
                    // $leavetype;
                }

                if (method_exists($leave, 'applied_by')) {
                    $appliedBy = $leave->applied_by()->getAttributes();
                    $data['applied_by'] = [
                        'first_name' => $appliedBy['first_name'],
                        'last_name' => $appliedBy['last_name'],
                    ];
                    // $appliedBy;
                }

                return $data;
            }, $query['data']);

            header('Content-Type: application/json');
            echo json_encode($query, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        } catch (\PDOException $e) {
            header('Content-Type: application/json', true, 500);
            echo json_encode(['error' => 'PDOException: ' . $e->getMessage()]);
        } finally {
            exit;
        }
    }

    public function updateAppliedleaveForm($id)
    {

        // die("Start");


        $appliedLeave = appliedLeave::model()->where(['id' => $id])->get();

        $idCheck = count($appliedLeave);

        if ($idCheck == 0) {
            View::redirect("/admin/appliedleaves", "appliedLeave not found", "warning", 302);
        }

        $headerTitle = 'Edit appliedLeave';
        $message = isset($_SESSION['message']) ? $_SESSION['message'] : null;
        $messageCode = isset($_SESSION['message_code']) ? $_SESSION['message_code'] : null;

        return View::render('admin.appliedleaves.edit', ['appliedleave' => $appliedLeave], $headerTitle, $message, $messageCode, 200);
    }

    public function show($id)
    {
        $appliedLeave = appliedLeave::model()->where(['id' => $id])->get();

        $idCheck = count($appliedLeave);

        if ($idCheck == 0) {
            View::redirect("/admin/appliedleaves", "appliedLeave not found", "warning", 302);
        }

        $headerTitle = 'Show appliedLeave';
        $message = isset($_SESSION['message']) ? $_SESSION['message'] : null;
        $messageCode = isset($_SESSION['message_code']) ? $_SESSION['message_code'] : null;

        return View::render('admin.appliedleaves.show', ['appliedleave' => $appliedLeave], $headerTitle, $message, $messageCode, 200);
    }

    // public function updateAppliedleave($id, $data)
    // {

    //     if (isset($data['status']) && isset($id)) {
    //         $leave = AppliedLeave::model()->find($id);

    //         if ($leave) {
    //             $leave->status = $data['status'];
    //             $leave->from_date = $data['from_date'];
    //             $leave->to_date = $data['to_date'];
    //             $updateSuccessful = $leave->update();



    //             if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

    //                 if ($updateSuccessful) {
    //                     echo json_encode(['status' => 'success', 'message' => 'Applied Leave updated successfully', 'redirect' => '/admin/appliedleaves']);
    //                     exit;
    //                 } else {
    //                     echo json_encode(['status' => 'danger', 'message' => 'Error occurred while updating Applied Leave', 'redirect' => false]);
    //                     exit;
    //                 }
    //             } else {
    //                 if ($updateSuccessful) {
    //                     View::redirect("/admin/appliedleaves", "Applied Leave updated successfully!", "success", 302);
    //                 } else {
    //                     View::redirect("/admin/appliedleaves", "Error occurred while updating Applied Leave", "danger", 302);
    //                 }
    //             }
    //         } else {
    //             if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    //                 echo json_encode(['status' => 'danger', 'message' => 'Applied Leave Not Found!', 'redirect' => false]);
    //                 exit;
    //             } else {
    //                 View::redirect("/admin/appliedleaves", "Applied Leave Not Found!", "danger", 302);
    //             }
    //         }
    //     }
    // }

    public function updateAppliedleave($id, $data)
    {
        if (isset($data['status']) && isset($id)) {

            $leave = AppliedLeave::model()->find($id);

            if ($leave) {

                // Get user (FIXED)
                $user = $leave->applied_by();

                $name  = $user->first_name . ' ' . $user->last_name;
                $email = $user->email;

                // Store previous status (to avoid duplicate emails)
                $previousStatus = $leave->status;

                // Update leave
                $leave->status = $data['status'];
                $leave->from_date = $data['from_date'];
                $leave->to_date = $data['to_date'];

                $updateSuccessful = $leave->update();

                // var_dump($updateSuccessful);
                // var_dump($previousStatus);

                // ✅ SEND EMAIL ONLY AFTER SUCCESSFUL UPDATE
                if ($updateSuccessful && $previousStatus !== $data['status']) {

                    if ($data['status'] === 'accepted') {

                        $subject = "Leave Approved";
                        $message = "
                            Your leave request has been <b>APPROVED</b>.<br><br>
                            <b>From:</b> {$leave->from_date}<br>
                            <b>To:</b> {$leave->to_date}
                        ";

                    } elseif ($data['status'] === 'rejected') {

                        $subject = "Leave Rejected";
                        $message = "
                            Your leave request has been <b>REJECTED</b>.<br><br>
                            <b>From:</b> {$leave->from_date}<br>
                            <b>To:</b> {$leave->to_date}
                        ";

                    } else {
                        $subject = "Leave Status Updated";
                        $message = "Your leave status is now: {$data['status']}";
                    }

                    // Send email
                    $this->sendLeaveEmail($name, $email, $subject, $message);
                }

                // EXISTING RESPONSE LOGIC (UNCHANGED)
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

                    if ($updateSuccessful) {
                        echo json_encode([
                            'status' => 'success',
                            'message' => 'Applied Leave updated successfully',
                            'redirect' => '/admin/appliedleaves'
                        ]);
                        exit;
                    } else {
                        echo json_encode([
                            'status' => 'danger',
                            'message' => 'Error occurred while updating Applied Leave',
                            'redirect' => false
                        ]);
                        exit;
                    }

                } else {

                    if ($updateSuccessful) {
                        View::redirect("/admin/appliedleaves", "Applied Leave updated successfully!", "success", 302);
                    } else {
                        View::redirect("/admin/appliedleaves", "Error occurred while updating Applied Leave", "danger", 302);
                    }
                }

            } else {

                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    echo json_encode(['status' => 'danger', 'message' => 'Applied Leave Not Found!', 'redirect' => false]);
                    exit;
                } else {
                    View::redirect("/admin/appliedleaves", "Applied Leave Not Found!", "danger", 302);
                }
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

    // public function updateAppliedleave($id, $data)
    // {
    //     if (isset($data['status']) && isset($id)) {

    //         $leave = AppliedLeave::model()->find($id);



    //         if ($leave) {

    //             $leave->status    = $data['status'];
    //             $leave->from_date = $data['from_date'];
    //             $leave->to_date   = $data['to_date'];

    //             $updateSuccessful = $leave->update();

    //             die($updateSuccessful);

    //             // ✅ Send email AFTER successful update
    //             if ($updateSuccessful) {

    //                 // Get employee details
    //                 $users = \app\models\User::model()
    //                     ->where(['id' => $leave->applied_by])
    //                     ->get();

    //                 if ($users && count($users) > 0) {

    //                     $employee = $users[0];

    //                     die($employee);


    //                     $name  = $employee->first_name . ' ' . $employee->last_name;
    //                     $email = $employee->email;

    //                     // Determine message based on status
    //                     if ($leave->status === 'approved') {

    //                         $subject = "Leave Approved";
    //                         $message = "Your leave from {$leave->from_date} to {$leave->to_date} has been APPROVED.";

    //                     } elseif ($leave->status === 'rejected') {

    //                         $subject = "Leave Rejected";
    //                         $message = "Your leave from {$leave->from_date} to {$leave->to_date} has been REJECTED.";

    //                     } else {
    //                         $subject = "Leave Update";
    //                         $message = "Your leave status has been updated to {$leave->status}.";
    //                     }

    //                     // Send email
    //                     $this->sendLeaveEmail($name, $email, $subject, $message);
    //                 }
    //             }

    //             // ================= EXISTING RESPONSE =================
    //             if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    //                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

    //                 if ($updateSuccessful) {
    //                     echo json_encode([
    //                         'status' => 'success',
    //                         'message' => 'Applied Leave updated successfully',
    //                         'redirect' => '/admin/appliedleaves'
    //                     ]);
    //                     exit;
    //                 } else {
    //                     echo json_encode([
    //                         'status' => 'danger',
    //                         'message' => 'Error occurred while updating Applied Leave',
    //                         'redirect' => false
    //                     ]);
    //                     exit;
    //                 }

    //             } else {

    //                 if ($updateSuccessful) {
    //                     View::redirect("/admin/appliedleaves", "Applied Leave updated successfully!", "success", 302);
    //                 } else {
    //                     View::redirect("/admin/appliedleaves", "Error occurred while updating Applied Leave", "danger", 302);
    //                 }
    //             }

    //         } else {

    //             if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    //                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

    //                 echo json_encode([
    //                     'status' => 'danger',
    //                     'message' => 'Applied Leave Not Found!',
    //                     'redirect' => false
    //                 ]);
    //                 exit;

    //             } else {
    //                 View::redirect("/admin/appliedleaves", "Applied Leave Not Found!", "danger", 302);
    //             }
    //         }
    //     }
    // }

    // public function updateAppliedleave($id, $data)
    // {
    //     if (isset($data['status']) && isset($id)) {

    //         $leave = AppliedLeave::model()->find($id);

    //         $user = $leave->applied_by;

    //         $name = $user->first_name . ' ' . $user->last_name;
    //         $email = $user->email;

    //         // die($data['status']);

    //         // ✉️ Determine message
    //         if ($data['status'] === 'accepted') {

    //             $subject = "Leave Approved";
    //             $message = "
    //                 Your leave request has been <b>APPROVED</b>.<br><br>
    //                 <b>From:</b> {$leave->from_date}<br>
    //                 <b>To:</b> {$leave->to_date}
    //             ";

    //         } elseif ($data['status'] === 'rejected') {

    //             $subject = "Leave Rejected";
    //             $message = "
    //                 Your leave request has been <b>REJECTED</b>.<br><br>
    //                 <b>From:</b> {$leave->from_date}<br>
    //                 <b>To:</b> {$leave->to_date}
    //             ";

    //         } else {
    //             $subject = "Leave Status Updated";
    //             $message = "Your leave status is now: {$data['status']}";

    //         }

    //         // $count = 1;

    //         // if ($count == 1){

    //         //     $this->sendLeaveEmail($name, $email, $subject, $message);
    //         //     $count--;
    //         // }
            

    //         if ($leave) {

    //             $leave->status    = $data['status'];
    //             $leave->from_date = $data['from_date'];
    //             $leave->to_date   = $data['to_date'];

    //             // $this->sendLeaveEmail($name, $email, $subject, $message);

    //             $updateSuccessful = $leave->update();

    //             // Optional: redirect with message
    //             if ($updateSuccessful) {
    //                 return View::redirect("/admin/appliedleaves", "Leave has been updated successfully", "success", 302);
    //             } else {
    //                 return View::redirect("/admin/appliedleaves", "Error updating leave", "danger", 302);
    //             }
    //         }
    //     }
    // }

    
}