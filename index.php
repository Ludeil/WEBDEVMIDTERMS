<?php
$pageTitle = 'Obeda Dormitories';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Affordable dormitories in Dumaguete City.">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="site-header" id="top">
    <a class="header-logo" href="#home" aria-label="Obeda Dormitories home">
        <img src="assets/images/logo-dark.svg" alt="Logo image dark">
    </a>
    <nav class="main-nav" aria-label="Main navigation">
        <a class="active" href="#home">HOME</a>
        <a href="#about">ABOUT US</a>
        <a href="#rooms">Rooms &amp; Amenities</a>
        <a href="#gallery">Gallery</a>
        <a href="#contact">Contact &amp; Location</a>
    </nav>
    <div class="account-nav">
        <a href="#signin">Sign in</a>
        <a class="signup" href="#signup">Sign up</a>
        <button class="user-icon" type="button" aria-label="Account"><img src="assets/images/user-dark.svg" alt="user logo"></button>
    </div>
</header>

<main>
    <!-- page home -->
    <section class="mock-page home-page" id="home">
        <div class="home-left">
            <img class="brand-image brand-main" src="assets/images/brand-light.svg" alt="Obeda Dormitories brand light bgggg">
            <img class="photo photo-building" src="assets/images/hero-building-image.jpg" alt="Dormitory building photoo">
            <div class="social-story">
                <img class="story-bg" src="assets/images/story-image.jpeg" alt="Dormitory exterior photoo">
                <div class="story-overlay"></div>
                <div class="story-copy">
                    <p>One of the many dormitories in Dumaguete,<br>however this is an initiative to make renting<br>affordable for everyone.</p>
                    <p>Try to catch more updates here:</p>
                    <div class="story-handle">
                        <img src="assets/images/chat-graphic.png" alt="Social iconnn">
                        <span>@Obedadormitories | OBdormz yt channel</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="home-right">
            <div class="hero-city-wrap">
                <img class="photo hero-city" src="assets/images/city-image.jpg" alt="Dumaguete city photoooo">
                <div class="hero-city-overlay"></div>
                <div class="hero-copy">
                    <h1>AFFORDABLE<br>DORMITORIES<br>IN DUMAGUETE<br>CITY.</h1>
                    <p>We have designed safe, clean and affordable living quarters.<br>For long term and short term applicants.</p>
                </div>
            </div>
            <div class="cta-row home-cta">
                <a class="cta-btn" href="#contact">Schedule Viewing</a>
                <span class="or">or</span>
                <a class="cta-btn" href="#rooms">Rent a Room</a>
            </div>
        </div>
    </section>

         <!-- page about us -->
    <section class="mock-page about-page" id="about">
        <div class="about-copy">
            <h2>A safe and affordable<br>dormitory in<br>Dumaguete city.</h2>
            <p class="about-lead">A dormitory with full certification,<br>we are regulated and are fully<br>operational for taking in new<br>dormates.</p>
            <p class="cert-list">| Business Registration (DTI/SEC) | BIR Registration | Fire Safety Inspection<br></p>
        </div>
        <div class="cert-panel">
            <div class="cert-card"><img src="assets/images/cert-dti-image.png" alt="Certification DTI"></div>
            <div class="cert-card"><img src="assets/images/cert-bir-image.png" alt="Certification BIR"></div>
            <div class="cert-card"><img src="assets/images/cert-bfp-image.jpg" alt="Certification FIREEE"></div> 
        </div>
        <div class="cta-row about-cta">
            <a class="cta-btn" href="#contact">Schedule Viewing</a>
            <span class="or">or</span>
            <a class="cta-btn" href="#rooms">Rent a Room</a>
        </div>
    </section>

    <!-- page rooms and ameneties -->
    <section class="mock-page rooms-page" id="rooms">
        <div class="bedspace-row">
            <img class="photo bedspace-photo" src="assets/images/bedspace-image.jpg" alt="4 person bedspacer photo">
            <div class="bedspace-copy">
                <h2>4 person Bedspacer</h2>
                <p>With Covered Amenities:<br>Wifi<br>Electricity<br>Water</p>
            </div>
        </div>

        <div class="female-band">
            <div class="female-copy">We Have Female<br>Exclusive Rooms</div>
            <img class="photo female-photo" src="assets/images/female-room-image.jpg" alt="Female exclusive room photo">
        </div>

        <div class="communal-row">
            <div class="communal-photos">
                <img class="photo kitchen-photo" src="assets/images/kitchen-image.jpg" alt="Communal kitchen photo">
                <img class="photo bathroom-photo" src="assets/images/bathroom-image.jpg" alt="Bathroom photo">
            </div>
            <div class="communal-copy">
                <h2>Communal Kitchen and<br>Bathroom</h2>
                <div class="cta-row communal-cta">
                    <a class="cta-btn small" href="#contact">Schedule Viewing</a>
                    <span class="or">or</span>
                    <a class="cta-btn small" href="#rooms">Rent a Room</a>
                </div>
            </div>
        </div>
    </section>

    <!-- page gallery -->
    <section class="mock-page gallery-page" id="gallery">
        <div class="watermark-grid" aria-hidden="true"></div>
        <h2>OUR DORMITORIES HAVE SHAPED<br>LEGENDS AND IS STILL SHAPING MORE</h2>

        <div class="gallery-stage" data-gallery>
            <button class="gallery-arrow prev" type="button" aria-label="Previous image">‹</button>
            <div class="gallery-slides">
                <img class="gallery-slide far-left" src="assets/images/gallery-1-image.svg" alt="Gallery  1">
                <img class="gallery-slide left" src="assets/images/gallery-2-image.svg" alt="Gallery  2">
                <img class="gallery-slide center" src="assets/images/gallery-3-image.svg" alt="Gallery  3">
                <img class="gallery-slide right" src="assets/images/gallery-4-image.svg" alt="Gallery  4">
                <img class="gallery-slide far-right" src="assets/images/gallery-5-image.svg" alt="Gallery  5">
            </div>
            <button class="gallery-arrow next" type="button" aria-label="Next image">›</button>
        </div>

        <h3>JOIN THE LEGENDS NOW!</h3>
        <div class="cta-row gallery-cta">
            <a class="cta-btn" href="#contact">Schedule Viewing</a>
            <span class="or">or</span>
            <a class="cta-btn" href="#rooms">Rent a Room</a>
        </div>
    </section>

    <!-- page footer -->
    <section class="contact-page" id="contact">
        <div class="contact-top">
            <div class="contact-locations">
                <div class="contact-heading">
                    <img src="assets/images/location-image.png" alt="Location icon">
                    <h2>WE HAVE DORMITORIES AT THESE<br>LOCATIONS IN DUMAGUETE CITY</h2>
                </div>
                <div class="locations-list">
                    <div>Daro</div>
                    <div>Piapi</div>
                    <div>Batinguel</div>
                    <div>Cadawinonan</div>
                    <div>Taclobo</div>
                </div>
            </div>

            <div class="contact-social">
                <div class="contact-social-heading">
                    <h3>CONTACT US THROUGH:</h3>
                    <img src="assets/images/phone-image.png" alt="Phone icon">
                </div>
                <div class="social-item">
                    <img src="assets/images/facebook-image.png" alt="Facebook icon ">
                    <strong>obedadormitories/fb</strong>
                </div>
                <div class="social-item">
                    <img src="assets/images/youtube-image.png" alt="YouTube icon placeholder">
                    <strong>OBdormz yt channelv</strong>
                </div>
            </div>
        </div>

        <footer class="site-footer">
            <div class="footer-brand">
                <img class="footer-user" src="assets/images/user-dark.svg" alt="Account icon ">
                <img class="brand-image brand-footer" src="assets/images/brand-placeholder-light1.svg" alt="Obeda Dormitories brand placeholder">
            </div>
            <div class="footer-copy-grid">
                <div>
                    <p>ALL RIGHTS<br>RESERVED.Lorem<br>ipsum dolor sit amet,</p>
                    <p>ALL RIGHTS<br>RESERVED.Lorem<br>ipsum dolor sit amet,</p>
                    <p>ALL RIGHTS<br>RESERVED.Lorem<br>ipsum dolor sit amet,</p>
                    <p>ALL RIGHTS<br>RESERVED.Lorem<br>ipsum dolor sit amet,</p>
                </div>
                <div>
                    <p>ALL RIGHTS<br>RESERVED.Lorem<br>ipsum dolor sit amet,</p>
                    <p>ALL RIGHTS<br>RESERVED.Lorem<br>ipsum dolor sit amet,</p>
                    <p>ALL RIGHTS<br>RESERVED.Lorem<br>ipsum dolor sit amet,</p>
                    <p>ALL RIGHTS<br>RESERVED.Lorem<br>ipsum dolor sit amet,</p>
                </div>
            </div>
            <div class="footer-social">
                <div class="footer-social-item">
                    <img src="assets/images/facebook-footer-image.png" alt="Facebook icon ">
                    <p>ALL RIGHTS<br>RESERVED.<br>Lorem<br>ipsum dolor<br>sit amet,</p>
                </div>
                <div class="footer-social-item">
                    <img src="assets/images/youtube-footer-image.png" alt="YouTube icon ">
                    <p>ALL RIGHTS<br>RESERVED.<br>Lorem<br>ipsum dolor<br>sit amet,</p>
                </div>
            </div>
            <div class="footer-bottom">ALL RIGHTS RESERVED.</div>
        </footer>
    </section>
</main>

<script src="assets/js/script.js"></script>
</body>
</html>
