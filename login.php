<?php
define("BASE_URL", "http://localhost/loginpages/");
define("BASE_DASHBOARD", "http://localhost/loginpages/dashboard/");
/* KONFIGURASI DATABASE */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'namadatabase');
function getDB() {
	$dbhost = DB_SERVER;
	$dbuser = DB_USERNAME;
	$dbpass = DB_PASSWORD;
	$dbname = DB_DATABASE;
	$dbConnection = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
	$dbConnection->exec("set names utf8");
	$dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbConnection;
}
$con = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
mysqli_set_charset($con, "utf8mb4");
// Check connection
if (!$con) {
	echo "Duh! Gagal menghubungi MySQL: " . mysqli_connect_error();
}
if (empty($_SESSION["memberid"])) {
?>
	<!DOCTYPE html>
	<html>
	<head>
		<?php //Header Web Disini; ?>
		<!-- Font Awesome -->
		<link rel="stylesheet" href="<?= BASE_URL; ?>assets/loginpage/css/all.min.css">
		<!-- Ionicons -->
		<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
		<!-- icheck bootstrap -->
		<link rel="stylesheet" href="<?= BASE_URL; ?>assets/loginpage/css/icheck-bootstrap.min.css">
		<!-- Theme style -->
		<link rel="stylesheet" href="<?= BASE_URL; ?>assets/loginpage/css/adminlte.min.css">
		<!-- Google Font: Source Sans Pro -->
		<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">		
	</head>

	<body class="hold-transition login-page">
		<div class="login-box">
			<div class="login-logo">
				<a href="<?php echo BASE_URL; ?>"><img src="<?= BASE_URL; ?>assets/images/logo.png" style="width:100%;"></a>
			</div>
			<!-- /.login-logo -->
			<div class="card">
				<div class="card-body login-card-body">
					<p class="login-box-msg"><strong>Silahkan Masuk!</strong><strong></strong></p>
					<?php
					if (isset($_POST["Submit"])) {
						$hpemail = trim($_POST["username"]);
						$password = trim($_POST["password"]);
						$usertipe = strpos($hpemail, "@");
						if ($usertipe > 0) { //apabila usermenggunakan email
							$u = explode("@", $hpemail);
							$username = $u[0];
							$email = $hpemail;
							$usql = mysqli_query($con, "SELECT * FROM member WHERE email='$email' AND blokir='0'");
						} else { //apabila daftar menggunakan nomor hp
							if (substr($hpemail, 0, 2) == "08") {
								$nohp = $hpemail;
								$usql = mysqli_query($con, "SELECT * FROM member WHERE hp='$nohp' AND blokir='0'");
							} else {
								$userlogin = $hpemail;
								$usql = mysqli_query($con, "SELECT * FROM member WHERE username='$userlogin' AND blokir='0'");
							}
						}

						if (mysqli_num_rows($usql) > 0) {
							$u = mysqli_fetch_assoc($usql);
							if ($u["status"] == "1") {
								$p1 = "\$X\$Z";
								$p2 = md5($password);
								$pass = $p1 . $p2;
								if ($u["password"] === $pass) {
									$valid_id = $u["id_member"];
									$valid_username = $u["username"];
									$valid_nama = $u["nama"];
									$valid_level = $u["level"];
									//akses diizinkan
									if (isset($_POST["remember"])) {
										//gunakan cookies
										$validlogin = array(
											'0' => 'loginpages',
											'1' => $valid_id,
											'2' => substr(md5($valid_username), 0, 13),
											'3' => strtoupper(substr(sha1($valid_nama), 0, 11)),
										);										
										setcookie("_nukggnwn", json_encode($validlogin), time() + 31556926); // kadaluarsa setelah 1 tahun
									} else {
										//gunakan session
										$_SESSION['validlogin'] = array(
											'0' => 'loginpages',
											'1' => $valid_id,
											'2' => substr(md5($valid_username), 0, 13),
											'3' => strtoupper(substr(md5($valid_nama), 0, 11)),
										);
									}
									//status online
									mysqli_query($con, "UPDATE member SET online='1' WHERE id_member='$valid_id'");
									//arahkan ke halaman asal
									header("Location: " . BASE_DASHBOARD);																	
								} else {
									//akses ditolak karena password tidak sama
									$pesan = "username atau password salah!";
								}
							} else {
								$pesan = "Username ga aktif, silahkan <a href='aktivasi.php'>aktifin</a> dulu.";
							}
						} else {
							//username tidak ditemukan	
							$pesan = "username ga dikenal!";
						}
					}
					if (isset($pesan)) {
						echo "<div style='text-align:center;color:red;font-size:12px;padding-bottom:10px;'>$pesan</div>";
					}
					?>

					<form action="" method="post">
						<div class="input-group mb-3">
							<input type="text" id="username" name="username" class="form-control" placeholder="Username/Email/HP" required title="masukin email" autocomplete>
							<div class="input-group-append">
								<div class="input-group-text">
									<span class="fas fa-user"></span>
								</div>
							</div>
						</div>
						<div class="input-group mb-3">
							<input type="password" id="password" name="password" class="form-control" placeholder="Password" required title="masukin password">
							<div class="input-group-append">
								<div class="input-group-text">
									<span class="far fa-eye" id="togglePassword"></span>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-8">
								<div class="icheck-primary">
									<input type="checkbox" id="remember" name="remember" checked>
									<label for="remember">
										Ingat selalu
									</label>
								</div>
							</div>
							<!-- /.col -->
							<div class="col-4">
								<input type="hidden" class="form-control" name="token" id="token">
								<input type="submit" id="submit" name="Submit" value="Masuk" class="btn btn-primary btn-block">
							</div>
							<!-- /.col -->
						</div>
					</form>

					<div class="social-auth-links text-center mb-3">
						<p>- atau -</p>
						<a href="loginwa.php" class="btn btn-block btn-success">
							<i class="fab fa-whatsapp mr-2"></i> Masuk dengan Whatsapp
						</a>
						<a href="login.php" class="btn btn-block btn-danger">
							<i class="fab fa-google mr-2"></i> Masuk dengan Google
						</a>
					</div>
					<!-- /.social-auth-links -->
					<hr>
					<div class="row">
						<div class="col-6" style="font-size:12px; text-align:center;">
							<p class="mb-1">
								<a href="lupa-password.php">Lupa password!</a>
							</p>
						</div>
						<div class="col-6" style="font-size:12px; text-align:center;">
							<p class="mb-0">
								<a href="daftar.php" class="text-center">Baru? Daftar disini!</a>
							</p>
						</div>
					</div>
				</div>
				<!-- /.login-card-body -->
			</div>
		</div>
		<!-- /.login-box -->
		<script src="<?= BASE_URL; ?>assets/loginpage/js/jquery.min.js"></script>
		<script src="<?= BASE_URL; ?>assets/loginpage/js/bootstrap.bundle.min.js"></script>
		<script src="<?= BASE_URL; ?>assets/loginpage/js/adminlte.min.js"></script>
	</body>

	</html>
<?php
} else {
	header("Location:" . BASE_DASHBOARD);
} ?>
