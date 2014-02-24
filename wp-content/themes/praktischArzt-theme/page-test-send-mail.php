<?php if (isset($_POST['email'])){
    if(wp_mail($_POST['email'], 'Test mail from praktischarzt', 'Test content')){
        echo '<h1>WORKS</h1>';    
    }   
    else{
        echo '<h1>FAIL</h1>';
    } 
}
?>
<form action="" method="post">
<input name="email" />
</form>
