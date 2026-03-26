<?php 
$user = $data ? $data->user : null;
?>

<div class="container-fluid px-4">

    <?php \app\messages\AlertMessage::display(); ?>

    <h4 class="mt-4">Profile</h4>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard</li>
        <li class="breadcrumb-item">Profile</li>
    </ol>

    <div class="row">

        <!-- LEFT: FORM -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h4>Edit Profile</h4>
                </div>

                <div class="card-body">

                    <!-- ✅ FORM START -->
                    <form action="/employee/profile/update" method="POST">

                        <!-- First Name -->
                        <div class="form-group mb-3">
                            <label>First Name</label>
                            <input 
                                name="first_name" 
                                type="text" 
                                class="form-control"
                                value="<?= $user->first_name ?>" 
                                required 
                            />
                        </div>

                        <!-- Last Name -->
                        <div class="form-group mb-3">
                            <label>Last Name</label>
                            <input 
                                name="last_name" 
                                type="text" 
                                class="form-control"
                                value="<?= $user->last_name ?>" 
                                required 
                            />
                        </div>

                        <!-- Email -->
                        <div class="form-group mb-3">
                            <label>Email</label>
                            <input 
                                name="email" 
                                type="email" 
                                class="form-control" 
                                value="<?= $user->email ?>" 
                                required 
                            />
                        </div>

                        <!-- Submit Button -->
                        <button 
                            type="submit" 
                            name="update_profile" 
                            class="btn btn-primary"
                        >
                            Update Profile
                        </button>

                    </form>
                    <!-- ✅ FORM END -->

                </div>
            </div>
        </div>

        <!-- RIGHT: PROFILE IMAGE -->
        <div class="col-md-3">
            <img src="/views/assets/img/avatar.png" class="d-block img-fluid mb-3">

            <!-- Optional delete (you can wire later) -->
            <button class="btn btn-danger w-100">Delete</button>
        </div>

    </div>
</div>