<?php
include 'functions.php';

$get_profile_id = (int)filter_input(INPUT_GET, 'profile_id');

if (isset($_SESSION['is_loged']) && ($_SESSION['user_info']['user_id'] == $get_profile_id)) {

    $stmt = run_q("SELECT * FROM users WHERE user_id = :get_profile_id", [":get_profile_id" => $get_profile_id]);
    if ($stmt && $row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $post_old_password = trim(filter_input(INPUT_POST, 'old_password'));
        $post_new_password = trim(filter_input(INPUT_POST, 'new_password'));
        $post_match_password = trim(filter_input(INPUT_POST, 'match_password'));
        $post_form_submit = (int)filter_input(INPUT_POST, 'form_submit');
        $post_signature = trim(filter_input(INPUT_POST, 'signature'));
        $post_submit_signature = (int)filter_input(INPUT_POST, 'submit_signature');

        if ($post_form_submit == 1) {
            $stmt_check = run_q("SELECT password FROM users WHERE user_id = :get_profile_id", [":get_profile_id" => $get_profile_id]);
            $row_check = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if ($row_check && password_verify($post_old_password, $row_check['password'])) {
                if ($post_new_password == $post_match_password) {
                    $hashed_password = password_hash($post_new_password, PASSWORD_DEFAULT);
                    $update_stmt = run_q("UPDATE users SET password = :hashed_password WHERE user_id = :get_profile_id", [":hashed_password" => $hashed_password, ":get_profile_id" => $get_profile_id]);

                    if ($update_stmt) {
                        redirect('index.php');
                    } else {
                        echo "Грешка при обновяване на паролата.";
                    }
                } else {
                    echo "Новите пароли не съвпадат.";
                }
            } else {
                echo "Грешна стара парола.";
            }
        }

        if ($post_submit_signature == 1) {
            $update_signature_stmt = run_q("UPDATE users SET signature = :post_signature WHERE user_id = :get_profile_id", [":post_signature" => $post_signature, ":get_profile_id" => $get_profile_id]);
            if ($update_signature_stmt) {
                redirect('index.php');
            } else {
                echo "Грешка при обновяване на подписа.";
            }
        }

        include 'template/header.php';
        echo '<div id="content">';
        echo 'Здравейте <b>' . htmlspecialchars($row['username'], ENT_QUOTES) . '</b>. Тук ще може да редактирате своя профил. Потребителското име не може да бъде променяно.';

        echo '
        <br><br><b>Промяна на парола:</b><hr>
        <form action="profile.php?profile_id=' . $get_profile_id . '" method="post">
            Стара парола: <input type="password" name="old_password"><br />
            Нова парола: <input type="password" name="new_password"><br />
            Повтори парола: <input type="password" name="match_password"><br />
            <input type="hidden" name="form_submit" value="1">
            <input type="hidden" name="post_profile_id" value="' . $get_profile_id . '">
            <input type="submit" value="Промени паролата си">
        </form>
        ';

        echo '
        <b>Подпис:</b><hr>
        <form action="profile.php?profile_id=' . $get_profile_id . '" method="post">
            <textarea name="signature" rows="10" cols="85">' . htmlspecialchars($row['signature'], ENT_QUOTES) . '</textarea>
            <input type="hidden" name="submit_signature" value="1">
            <input type="hidden" name="post_profile_id" value="' . $get_profile_id . '">
            <input type="submit" value="Подпис">
        </form>
        ';

        echo '
        <b>Смяна на аватар:</b><hr>
        <form id="avatarForm" action="upload-avatar.php?profile_id=' . $get_profile_id . '" method="post" enctype="multipart/form-data">
            <input type="file" name="imageUpload" accept="image/*" required><br />
            <input type="hidden" name="profile_id" value="' . $get_profile_id . '">
            <input type="submit" value="Качи аватар">
        </form>
        ';

        echo '
        <script>
            document.getElementById("avatarForm").addEventListener("submit", function(event) {
                event.preventDefault();
                var formData = new FormData(this);

                fetch("upload-avatar.php?profile_id=' . $get_profile_id . '", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    if (data.startsWith("/")) {
                        alert("Аватарът беше успешно качен!");
                        location.reload();
                    } else {
                        alert(data);
                    }
                })
                .catch(error => {
                    console.error("Грешка при качване на файл:", error);
                });
            });
        </script>
        ';

        echo '</div>';
        include 'template/footer.php';

    } else {
        echo "Потребителят не е намерен.";
        exit;
    }

} else {
    redirect('index.php');
}
?>