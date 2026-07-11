<?php ?>
<!DOCTYPE html>
<html>
    <head>
        <title>Registration Form</title>
        <style>
            body{
                font-family: sans-serif;
                background-color:#FFF9E3;
            }
            .registeration{
                box-sizing:border-box;
                background-color:#e4eb84;
                margin: 24px auto;
                border-radius: 15px;
                padding:20px;
                width:min(92vw, 420px);
            }
            .registeration label,
            .registeration input{
                display:block;
                width:100%;
            }
            .registeration label{
                margin: 12px 0 6px;
            }
            .registeration input[type="checkbox"],
            .registeration input[type="radio"]{
                display:inline-block;
                width:auto;
                margin-right:6px;
            }
            .registeration .inline-group{
                margin-top: 6px;
            }
            
        </style>
    </head>
    <body>
        <h1 style="text-align:center; text-decoration:underline;">Registeration Form</h1>
        <div class="registeration">
        <form method="POST">
        <label for="name">Name</label>
        <input id="name" name="name" type="text" placeholder="Enter Name" required>
        <label for="email">Email</label>
        <input id="email" name="email" type="email" placeholder="Enter Mail ID" required>
        <label for="phone">Phone Number</label>
        <input id="phone" name="phone" type="tel" placeholder="Enter phone Number" pattern="[0-9]{10}" maxlength="10" required>
        <label>Hobbies</label>
        <div class="inline-group">
            <input type="checkbox" name="hobbies" value="Cricket">Cricket
            <input type="checkbox" name="hobbies" value="Football">Football
            <input type="checkbox" name="hobbies" value="Chess">Chess
            <input type="checkbox" name="hobbies" value="Ludo">Ludo
        </div>
        <label for="dob">DOB</label>
        <input id="dob" name="dob" type="date" required>
        <label>Gender</label>
        <div class="inline-group">
            <input type="radio" name="gender" value="Male" required>Male
            <input type="radio" name="gender" value="Female">Female
        </div>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" placeholder="Enter password" minlength="6" required>
        <br><br>
        <button type="submit">Submit</button>
        <button type="reset">Reset</button>
        </form>
        </div>
    </body>
</html>