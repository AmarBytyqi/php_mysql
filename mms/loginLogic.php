<?php
session_start();
include_once('config.php');
if(isset($_POST['submit'])){
    $username=$_POST['username'];
    $password=$_POST['password'];

    if(empty($username) || empty($password)){
        echo "Please fill all the fields!";
    }else{
        $sql="SELECT id,name,username,email,password, is_admin FROM users WHERE username=:username";
        $selectUser=$conn->prepare($sql);
        $selectUser->bindParam(":username",$username);
        $selectUser->execute();
        $data=$selectUser->fetch();

        if($data==false){
            echo "User does not exist!"
        }else{
            if(password_verify($password,$data['password'])){
                $_SESSION['id']=$data['id'];
                $_SESSION['username']=$data['username'];
                $_SESSION['email']=$data['email'];
                $_SESSION['name']=$data['name'];
                $_SESSION['is_admin']=$data['is_admin'];
                header('Location:dashboard.php');
            }else{
                echo "The password is wrong";
            }
        }
    }
}



?>