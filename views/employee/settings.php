<div class="container-fluid px-4">

    <?php \app\messages\AlertMessage::display(); ?>

    <h4 class="mt-4">Settings</h4>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard</li>
        <li class="breadcrumb-item">Settings</li>
    </ol>

    <div class="row">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h4>Change Password</h4>
                </div>

                <div class="card-body">
                    <form action="/employee/settings/password" method="POST">

                        <div class="form-group mb-3">
                            <label>Current Password</label>
                            <input
                                name="current_password"
                                type="password"
                                class="form-control"
                                required
                            />
                        </div>

                        <div class="form-group mb-3">
                            <label>New Password</label>
                            <input
                                name="new_password"
                                type="password"
                                class="form-control"
                                minlength="8"
                                required
                            />
                        </div>

                        <div class="form-group mb-3">
                            <label>Confirm New Password</label>
                            <input
                                name="confirm_password"
                                type="password"
                                class="form-control"
                                minlength="8"
                                required
                            />
                        </div>

                        <button
                            type="submit"
                            name="update_password"
                            class="btn btn-primary"
                        >
                            Update Password
                        </button>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
