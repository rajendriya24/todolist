<?php
session_start();

if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = array(
        array("id" => 1, "title" => "Belajar PHP", "status" => "belum"),
        array("id" => 2, "title" => "Kerjakan tugas UX", "status" => "selesai")
    );
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_task'])) {
        $newId = count($_SESSION['tasks']) > 0 ? max(array_column($_SESSION['tasks'], 'id')) + 1 : 1;
        $newTask = array(
            'id' => $newId,
            'title' => trim($_POST['task_title']),
            'status' => 'belum'
        );
        $_SESSION['tasks'][] = $newTask;
    }

    if (isset($_POST['toggle_status']) && isset($_POST['task_id'])) {
        foreach ($_SESSION['tasks'] as &$task) {
            if ($task['id'] == $_POST['task_id']) {
                $task['status'] = $task['status'] === 'selesai' ? 'belum' : 'selesai';
                break;
            }
        }
    }

    if (isset($_POST['delete_task']) && isset($_POST['task_id'])) {
        $_SESSION['tasks'] = array_filter($_SESSION['tasks'], function($task) {
            return $task['id'] != $_POST['task_id'];
        });
        $_SESSION['tasks'] = array_values($_SESSION['tasks']);
    }

    if (isset($_POST['edit_task']) && isset($_POST['task_id']) && isset($_POST['new_title'])) {
        foreach ($_SESSION['tasks'] as &$task) {
            if ($task['id'] == $_POST['task_id']) {
                $task['title'] = trim($_POST['new_title']);
                break;
            }
        }
    }

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>To-Do List App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .completed {
            background-color: #d4edda !important;
            color: #155724 !important;           
            font-weight: 500;
            transition: background-color 0.3s ease, color 0.3s ease;
            border-left: 5px solid #28a745;
        }
        .container {
            max-width: 800px;
            margin-top: 30px;
        }
        .edit-form {
            display: none;
        }
        table td input[type="checkbox"] {
            transform: scale(1.3);
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="container">
    <header class="text-center mb-5">
        <h1 class="display-4">To-Do List App</h1>
        <p class="lead">Kelola tugas Anda dengan mudah</p>
    </header>

    <!-- Form Tambah Tugas -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="" method="post" class="row g-3">
                <div class="col-md-8">
                    <input type="text" name="task_title" class="form-control" placeholder="Masukkan tugas baru..." required>
                </div>
                <div class="col-md-4">
                    <button type="submit" name="add_task" class="btn btn-primary w-100">Tambah Tugas</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Daftar Tugas -->
    <div class="card">
        <div class="card-body">
            <h2 class="card-title h4 mb-4">Daftar Tugas Anda</h2>
            <?php
            if (empty($_SESSION['tasks'])) {
                echo '<div class="alert alert-info">Tidak ada tugas. Tambahkan tugas pertama Anda!</div>';
            } else {
                echo '<div class="table-responsive">';
                echo '<table class="table table-striped table-hover align-middle text-center">';
                echo '<thead><tr>';
                echo '<th width="5%">No</th>';
                echo '<th width="50%">Nama Tugas</th>';
                echo '<th width="15%">Status</th>';
                echo '<th width="30%">Aksi</th>';
                echo '</tr></thead><tbody>';

                $counter = 1;
                foreach ($_SESSION['tasks'] as $task) {
                    $isCompleted = $task['status'] === 'selesai';
                    echo '<tr class="'.($isCompleted ? 'completed' : '').'">';
                    echo '<td>'.$counter.'</td>';
                    echo '<td class="text-start">';
                    echo '<div class="task-title">'.htmlspecialchars($task['title']).'</div>';
                    echo '<form action="" method="post" class="edit-form row g-2">';
                    echo '<div class="col-8">';
                    echo '<input type="text" name="new_title" class="form-control form-control-sm" value="'.htmlspecialchars($task['title']).'" required>';
                    echo '</div><div class="col-4">';
                    echo '<input type="hidden" name="task_id" value="'.$task['id'].'">';
                    echo '<button type="submit" name="edit_task" class="btn btn-sm btn-success">Simpan</button>';
                    echo '<button type="button" class="btn btn-sm btn-secondary cancel-edit">Batal</button>';
                    echo '</div></form></td>';

                    // Kolom Status di tengah
                    echo '<td>';
                    echo '<form action="" method="post" class="d-inline-block">';
                    echo '<input type="hidden" name="task_id" value="'.$task['id'].'">';
                    echo '<input type="hidden" name="toggle_status" value="1">';
                    echo '<input type="checkbox" onchange="this.form.submit()" '.($isCompleted ? 'checked' : '').'>';
                    echo '</form>';
                    echo '</td>';

                    // Kolom Aksi
                    echo '<td>';
                    echo '<button type="button" class="btn btn-sm btn-warning edit-btn me-2">Edit</button>';
                    echo '<form action="" method="post" class="d-inline">';
                    echo '<input type="hidden" name="task_id" value="'.$task['id'].'">';
                    echo '<button type="submit" name="delete_task" class="btn btn-sm btn-danger">Hapus</button>';
                    echo '</form>';
                    echo '</td>';

                    echo '</tr>';
                    $counter++;
                }
                echo '</tbody></table></div>';
            }
            ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editButtons = document.querySelectorAll('.edit-btn');
        const cancelButtons = document.querySelectorAll('.cancel-edit');

        editButtons.forEach(button => {
            button.addEventListener('click', function () {
                const row = this.closest('tr');
                const titleDiv = row.querySelector('.task-title');
                const editForm = row.querySelector('.edit-form');
                titleDiv.style.display = 'none';
                editForm.style.display = 'flex';
                editForm.style.alignItems = 'center';
            });
        });

        cancelButtons.forEach(button => {
            button.addEventListener('click', function () {
                const form = this.closest('.edit-form');
                const row = form.closest('tr');
                const titleDiv = row.querySelector('.task-title');
                form.style.display = 'none';
                titleDiv.style.display = 'block';
            });
        });
    });
</script>
</body>
</html>
