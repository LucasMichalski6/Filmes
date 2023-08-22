<?php

   require_once("globals.php");
   require_once("db.php");
   require_once("models/User.php");
   require_once("models/Message.php");
   require_once("dao/UserDAO.php");

   $message = new Message($BASE_URL);

   $userDao= new UserDAO($conn, $BASE_URL);

   //verifica o tipo do usuario
   $type = filter_input(INPUT_POST, "type");

   //verificação do tipo de formulario;

   if($type === "register") {

    $name = filter_input(INPUT_POST, "name");
    $lastname = filter_input(INPUT_POST, "lastname");
    $email = filter_input(INPUT_POST, "email");
    $password = filter_input(INPUT_POST, "password");
    $confirmpassword = filter_input(INPUT_POST, "confirmpassword");

    //Verificação de dados minimos
    if($name && $lastname && $email && $password) {
        
     //verificar se as senhas batem
      if($password === $confirmpassword) {
        
        //verificar se o email ja esta cadastrado no sistema
        if($userDao->findByEmail($email) === false) {

            $user = new User();

            // criação de token e senha
            $userToken = $user->generateToken();
            $finalPassword = $user->generatePassword($password);

            $user->name = $name;
            $user->lastname = $lastname;
            $user->email = $email;
            $user->password = $finalPassword;
            $user->token = $userToken;

            $auth = true;

            $userDao->create($user, $auth);

        } else {
            //Enviar mensagem de erro, usuario ja cadastrado
           $message->setMessage("Usuario já cadastrado.", "error", "back");
        }

        
      } else {

        //Enviar mensagem de erro, de dados faltantessenhas não batem
        $message->setMessage("As senhas não são iguais.", "error", "back");
      }

    } else {

        //Enviar mensagem de erro, de dados faltantes
        $message->setMessage("Por favor, preencha todos os campos.", "error", "back");

    }
    

   }else if($type === "login") {

    $email = filter_input(INPUT_POST, "email");
    $password = filter_input(INPUT_POST, "password");

    // Tenta autenticar usuario

    if($userDao->authenticateUser($email, $password)) {

      $message->setMessage("Seja bem vindo!", "error", "editprofile.php");

     //Redirecionar o usuario, caso não consiga autenticar 
    } else {

      $message->setMessage("Usuario ou senha incorretos", "error", "back");

    }

   } else {

    $message->setMessage("Informações invalidas.", "error", "index.php");


   }