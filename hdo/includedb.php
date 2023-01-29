<?php
    // NEMAZAT - nacita se odevsad
    
    $openweather_api = "916e26607461b7d5b5c3fad8075fa22e";
    
    $databaseConnection = new mysqli("db1.rojicek.cz","u_rojicek","VLDEQlrtc66BAwn4wNGF", "rs__rojicek_cz");

    if ($databaseConnection->connect_error)
    {
        mail('jiri@rojicek.cz', 'DB down !', $databaseConnection->connect_error);
        die("Database selection failed: " . $databaseConnection->connect_error);        
    }
    
    
?>