<main id="forgot-password">
    <?php require __DIR__ ."/messages/message.php"; ?>
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12 col-md-9">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-lg-6 d-none d-lg-block bg-password-image bg-blue"></div>
                        <div class="col-lg-6">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-2">Create New Password</h1>
                                    <p class="mb-4 text-success">Reset your password before reset link expires!</p>
                                    <form class="user" action="/new_password" method="post">
                                        <input type="hidden" name="code" value="<?= htmlspecialchars($data->token ?? '') ?>">
                                        <div class="form-group mb-2">
                                            <input type="password" name="new_password" class="form-control form-control-user"
                                                id="exampleInputpassword" aria-describedby="passwordHelp"
                                                placeholder="Enter New Password" required minlength="8">
                                        </div>
                                        <div class="form-group mb-2">
                                            <input type="password" name="confirm_password" class="form-control form-control-user"
                                                id="exampleInputconfirmpassword" aria-describedby="emailHelp"
                                                placeholder="Enter Confirmation Password" required minlength="8">
                                        </div>
                                        <button type="submit" name="reset_password"
                                            class="btn btn-primary btn-user btn-block">Reset
                                            password</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

