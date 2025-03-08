<?php
include("config.php");

if (isset($_SESSION['user_id'])) {
    if(isset($_SERVER['HTTP_REFERER'])) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        header("Location: https://mrtc.cc");
    }
    exit();
}
?>

<!DOCTYPE html>
<head>
        <link rel="stylesheet" type="text/css" href="css/style.css" />
        <link rel="stylesheet" type="text/css" href="css/cssfx.css" />
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        <title>PatchPulse</title>
        <meta charset="UTF-8" />
    </head>
<html>

    <section class="layout">
            <div class="header">
                <img
                    class="headerIcon"
                    src="images/PatchPulseLogo.svg"
                    alt="CentredLogo"
                />
                <span
                    ><a href="https://mrtc.cc" class="headerButton">Home</a></span
                >

		        <!-- Inizio V3.8-->
                        <span><div class="dropdown">
                            <a href="#" class="headerButton">Tool</a>
                            <div class="dropdown-content">
                                <a href="#">Fast Scan</a>
                                <a href="#">Web Scan</a>
                                <a href="#">Port Scan</a>
                                <a href="#">Custom Scan</a>
                            </div>
                        </div></span>
                <!-- Fine V3.8-->
		
                <span
                    ><a href="" class="headerButton">Contact Us</a></span
                >
                <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Se l'utente è loggato, mostra "ACCOUNT" -->
                        <button class="inout-button" onclick="window.location.href='html/accountPage.php'">Account</button>
                    <?php else: ?>
                        <!-- Se l'utente non è loggato, mostra "LOGIN" -->
                        <button class="inout-button" onclick="window.location.href='loginPage.php'">Log In / Sign Up</button>
                    <?php endif; ?>
            </div>
        </section>
        <h1 class="logRegText">Login</h1>
        <div class="boxWhite">
            <div class="form-container">
                <form action="dataBase/login_user.php" method="post" style="width: 45vw">
                    <input
                        type="email"
                        name="EmailOfUser"
                        id=""
                        placeholder="email"
                        required
                        class="form1"
                    />
                    <div class="password-container">
                        <input
                            type="password"
                            name="PasswordOfUserUnCrypt"
                            id="passwordField"
                            placeholder="password"
                            required
                            class="form1"
                        />
                        <span id="togglePassword" class="pwd-toggle">👁</span>
                    </div>
                    <div
                        class="cf-turnstile"
                        data-sitekey="0x4AAAAAAA45DIVnAjfWbKkG"
                    ></div>
                    <input
                        type="submit"
                        name=""
                        id=""
                        value="Log In"
                        class=""
                    />
                </form>
                <p class="p3">
                    <a href="registerPage.php" class="signIn"
                        >I forgot my password</a
                    >
                </p>
                <p class="p3">
                    <a href="registerPage.php" class="signIn">Register</a>
                </p>
 
	    <?php if (!empty($_SESSION['login_message'])): ?>
                <p style="text-align: center; font-weight: bold; color:white;">
            <?php echo htmlspecialchars($_SESSION['login_message']); ?>
                </p>
            <?php unset($_SESSION['login_message']); ?>
            <?php endif; ?>

           </div>
        </div>
        <hr class="sectionLine" />
        <div class="fot1">
            <ul>
                <li>
                    <img class="arrowsFot" src="images/arrow1.png" alt="" />
                    <a href="" class="faqButton" data-faq="faq1">Github</a>
                </li>
                <li>
                    <img class="arrowsFot" src="images/arrow1.png" alt="" />
                    <a href="" class="faqButton" data-faq="faq1">Contact Us</a>
                </li>
                <li>
                    <img class="arrowsFot" src="images/arrow1.png" alt="" />
                    <a href="" class="faqButton" data-faq="faq1"
                        >Our Organization</a
                    >
                </li>
            </ul>
        </div>
        <hr class="sectionLine" />
        <script src="script.js"></script>

    </body>

</html>
