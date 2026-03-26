<?php
$profile = $data ? $data->user[0] : null;
\app\messages\AlertMessage::display(); 
?>

<!-- PROFILE -->
<div class="container-fluid px-4">
  <h4 class="mt-4">Profile</h4>
  <ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Dashboard</li>
    <li class="breadcrumb-item ">Profile</li>
  </ol>
  <div class="row">
    <div class="col-md-9">
      <div class="card">
        <div class="card-header">
          <h4>Edit Profile</h4>
        </div>
        <div class="card-body">
          <!-- <form>
            <div class="form-group">
              <label for="name">Name</label>
              <input name="name" type="text" class="form-control"
                value="<?= $profile->first_name . ' ' . $profile->last_name ?>" />
            </div>
            <div class="form-group">
              <label for="email">Email</label>
              <input name="email" type="email" class="form-control" value="<?= $profile->email ?>" />
            </div>
            
          </form> -->
          <form action="/admin/profile/update" method="POST">

            <div class="form-group">
              <label>First Name</label>
              <input name="first_name" type="text" class="form-control"
                value="<?= $profile->first_name ?>" required />
            </div>

            <div class="form-group">
              <label>Last Name</label>
              <input name="last_name" type="text" class="form-control"
                value="<?= $profile->last_name ?>" required />
            </div>

            <div class="form-group">
              <label>Email</label>
              <input name="email" type="email" class="form-control"
                value="<?= $profile->email ?>" required />
            </div>

            <button type="submit" name="update_profile" class="btn btn-primary">
              Update Profile
            </button>

          </form>

        </div>
      </div>
    </div>
    <div class="col-md-3 mt-4">
      <img src="/views/assets/img/avatar.png" class="d-block img-fluid mb-3">
      <button class="btn btn-primary btn-block">Update</button>
      <button class="btn btn-danger btn-block">Delete</button>
    </div>
  </div>
</div>

<?php
require __DIR__ . "/components/footer.php";