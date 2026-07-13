<?php $activities = $data ? $data->activities : null; ?>
<div class="container-fluid px-4">

    <?php \app\messages\AlertMessage::display(); ?>

    <h4 class="mt-4">Activity Log</h4>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard</li>
        <li class="breadcrumb-item">Activity Log</li>
    </ol>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Leave Activity</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Activity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($activities): ?>
                                    <?php foreach ($activities as $activity): ?>
                                        <tr>
                                            <td><?= $activity->created_at ?></td>
                                            <td><?= htmlspecialchars($activity->description) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2">No activity yet</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
