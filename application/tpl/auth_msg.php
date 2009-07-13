<div id="auth-msg">
	<?
		print htmlspecialchars($_SESSION['auth.message']);
		unset($_SESSION['auth.message']);
	?>
</div>
