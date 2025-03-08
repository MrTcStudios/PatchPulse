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
<html>

<head>
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        <link rel="stylesheet" type="text/css" href="css/style.css" />
        <link rel="stylesheet" type="text/css" href="css/cssfx.css" />
        <title>PatchPulse</title>
        <meta charset="UTF-8" />
    </head>
    <body>
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
                    ><a href="mailto:support@mrtc.cc" class="headerButton">Contact Us</a></span
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
        <h1 class="logRegText">Register</h1>
        <div class="boxWhite">
            <div class="form-container">
                <form action="dataBase/save_user.php" method="post" style="width: 45vw">
                    <input
                        type="text"
                        name="NameOfUser"
                        id=""
                        placeholder="username"
                        requirede
                        class="form1"
                    />
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
                    <div class="terms-container">
                        <div class="terms-text">
                            <p class="p2">I have read and agree with the</p>
                            <p class="p3">
                                <a href="policy/privacy.php" class="signIn" id="termsLink"
                                    >Privacy Policy</a
                                >
                            </p>
                        </div>
                        <input
                            type="checkbox"
                            name="AgreeTerms"
                            id=""
                            required=""
                            class="terms-checkbox"
                        />
                    </div>
                    <div
                        class="cf-turnstile"
                        data-sitekey="0x4AAAAAAA45DIVnAjfWbKkG"
                    ></div>
                    <input
                        type="submit"
                        name=""
                        id=""
                        value="Register"
                        class=""
                    />
                </form>
                <p class="p3">
                    <a href="loginPage.php" class="signIn"
                        >I already have an account</a
                    >
                </p>

	    <?php if (!empty($_SESSION['registration_message'])): ?>
                <p style="text-align: center; font-weight: bold; color:white;">
            <?php echo htmlspecialchars($_SESSION['registration_message']); ?>
                </p>
            <?php unset($_SESSION['registration_message']); ?>
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
            </div>
        </div>

        <script src="script.js"></script>

    <body>


</html>
