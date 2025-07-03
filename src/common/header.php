<header style="color: rgb(255,255,255);">
        <h1>Portale Universitario</h1>
        <div class="userInfo" style="top: 0; align-items: center;">
            <span style="top: -30px;">Prof. <?php echo $_SESSION['nome'].' '.$_SESSION['cognome'];?></span>
        <div class="tendina">
            <button class="tendinaButton" type="button" onclick="toggleMenu()" >☰</button>
            <ul class="menuTendina" style="">
                <?php echo '<li style="margin-bottom: 20px;"><a href="../profilo/profilo.php" style="color:blue; ;text-decoration: none;">Profilo</a></li>'; 
                      echo '<li><a href="../logout/logout.php" style="color:red; text-decoration: none;">Logout</a></li>'
                ?>

            </ul>
        </div>
        </div>
</header>