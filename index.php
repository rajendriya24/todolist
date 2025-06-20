<?php
// Inisialisasi session untuk menyimpan tasks
session_start();

// Jika session tasks belum ada, inisialisasi dengan data default
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = array(
        array("id" => 1, "title" => "Belajar PHP", "status" => "belum"),
        array("id" => 2, "title" => "Kerjakan tugas UX", "status" => "selesai")
    );
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Tambah tugas baru
    if (isset($_POST['add_task'])) {
        $newId = count($_SESSION['tasks']) > 0 ? max(array_column($_SESSION['tasks'], 'id')) + 1 : 1;
        $newTask = array(
            'id' => $newId,
            'title' => trim($_POST['task_title']),
            'status' => 'belum'
        );
        array_push($_SESSION['tasks'], $newTask);
    }
    
    // Ubah status tugas
    if (isset($_POST['toggle_status']) && isset($_POST['task_id'])) {
        foreach ($_SESSION['tasks'] as &$task) {
            if ($task['id'] == $_POST['task_id']) {
                // Toggle status antara 'belum' dan 'selesai'
                if ($task['status'] === 'selesai') {
                    $task['status'] = 'belum'; // Jika selesai, ubah ke belum
                } else {
                    $task['status'] = 'selesai'; // Jika belum, ubah ke selesai
                }
                break;
            }
        }
    }

    // Hapus tugas
    if (isset($_POST['delete_task']) && isset($_POST['task_id'])) {
        $_SESSION['tasks'] = array_filter($_SESSION['tasks'], function($task) {
            return $task['id'] != $_POST['task_id'];
        });
        // Reindex array
        $_SESSION['tasks'] = array_values($_SESSION['tasks']);
    }
    
    // Edit tugas
    if (isset($_POST['edit_task']) && isset($_POST['task_id']) && isset($_POST['new_title'])) {
        foreach ($_SESSION['tasks'] as &$task) {
            if ($task['id'] == $_POST['task_id']) {
                $task['title'] = trim($_POST['new_title']);
                break;
            }
        }
    }
    
    // Redirect untuk menghindari resubmission
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced To-Do List</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .completed {
            text-decoration: line-through;
            color: #6c757d;
        }
        .container {
            max-width: 800px;
            margin-top: 30px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 14px;
        }
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .edit-form {
            display: none;
        }
        .edit-btn {
            margin-right: 5px;
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
                        <button type="submit" name="add_task" class="btn btn-primary w-100">
                            Tambah Tugas
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Daftar Tugas dalam Table -->
        <div class="card">
            <div class="card-body">
                <h2 class="card-title h4 mb-4">Daftar Tugas Anda</h2>
                
                <?php
                // Tampilkan daftar tugas dalam table
                if (empty($_SESSION['tasks'])) {
                    echo '<div class="alert alert-info">Tidak ada tugas. Tambahkan tugas pertama Anda!</div>';
                } else {
                    echo '<div class="table-responsive">';
                    echo '<table class="table table-striped table-hover">';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th width="5%">No</th>';
                    echo '<th width="50%">Nama Tugas</th>';
                    echo '<th width="20%">Status</th>';
                    echo '<th width="25%">Aksi</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    
                    $counter = 1;
                    foreach ($_SESSION['tasks'] as $task) {
                        $isCompleted = $task['status'] === 'selesai';
                        $statusClass = $isCompleted ? 'status-completed' : 'status-pending';
                        
                        echo '<tr class="'.($isCompleted ? 'completed' : '').'">';
                        echo '<td>'.$counter.'</td>';
                        echo '<td>';
                        
                        // Display task title and edit form
                        echo '<div class="task-title">'.htmlspecialchars($task['title']).'</div>';
                        echo '<form action="" method="post" class="edit-form row g-2">';
                        echo '<div class="col-8">';
                        echo '<input type="text" name="new_title" class="form-control form-control-sm" value="'.htmlspecialchars($task['title']).'" required>';
                        echo '</div>';
                        echo '<div class="col-4">';
                        echo '<input type="hidden" name="task_id" value="'.$task['id'].'">';
                        echo '<button type="submit" name="edit_task" class="btn btn-sm btn-success">Simpan</button>';
                        echo '<button type="button" class="btn btn-sm btn-secondary cancel-edit">Batal</button>';
                        echo '</div>';
                        echo '</form>';
                        
                        echo '</td>';
                        echo '<td><span class="status-badge '.$statusClass.'">'.($isCompleted ? 'Selesai' : 'Belum').'</span></td>';
                        echo '<td>';
                        
                        // Edit button
                        echo '<button type="button" class="btn btn-sm btn-warning edit-btn">Edit</button>';
                        
                        // Form untuk toggle status
                        echo '<form action="" method="post" class="d-inline me-2">';
                        echo '<input type="hidden" name="task_id" value="'.$task['id'].'">';
                        echo '<div class="form-check form-switch d-inline-block me-3">';
                        echo '<input class="form-check-input" type="checkbox" name="toggle_status" onchange="this.form.submit()" '.($isCompleted ? 'checked' : '').'>';
                        echo '<label class="form-check-label">'.($isCompleted ? 'Selesai' : 'Belum').'</label>';
                        echo '</div>';
                        echo '</form>';
                        
                        // Form untuk hapus tugas
                        echo '<form action="" method="post" class="d-inline">';
                        echo '<input type="hidden" name="task_id" value="'.$task['id'].'">';
                        echo '<button type="submit" name="delete_task" class="btn btn-sm btn-danger">';
                        echo 'Hapus';
                        echo '</button>';
                        echo '</form>';
                        
                        echo '</td>';
                        echo '</tr>';
                        
                        $counter++;
                    }
                    
                    echo '</tbody>';
                    echo '</table>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript untuk toggle form edit
        document.addEventListener('DOMContentLoaded', function() {
            const editButtons = document.querySelectorAll('.edit-btn');
            const cancelButtons = document.querySelectorAll('.cancel-edit');
            
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const titleDiv = row.querySelector('.task-title');
                    const editForm = row.querySelector('.edit-form');
                    
                    titleDiv.style.display = 'none';
                    editForm.style.display = 'flex';
                    editForm.style.alignItems = 'center';
                });
            });
            
            cancelButtons.forEach(button => {
                button.addEventListener('click', function() {
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