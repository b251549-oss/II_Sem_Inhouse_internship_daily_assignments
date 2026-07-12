<?php
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Registration Form</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body{
                font-family: Arial, Helvetica, sans-serif;
                margin: 0;
                min-height: 100vh;
                background: #f3efe6;
                color: #252525;
            }
            .registeration{
                box-sizing:border-box;
                background: #fffaf2;
                margin: 36px auto;
                border: 1px solid #d9d1c2;
                border-radius: 12px;
                padding: 24px;
                width: min(92vw, 460px);
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
            }
            h1{
                margin: 28px 0 0;
                text-align:center;
                font-size: 1.8rem;
                font-weight: 600;
                letter-spacing: 0.2px;
            }
            .registeration label,
            .registeration input,
            .registeration select,
            .registeration textarea{
                display:block;
                width:100%;
            }
            .registeration label{
                margin: 14px 0 6px;
                font-size: 0.95rem;
            }
            .registeration input,
            .registeration select,
            .registeration textarea{
                box-sizing: border-box;
                border: 1px solid #c9c1b1;
                border-radius: 8px;
                padding: 10px 12px;
                background: #ffffff;
                font: inherit;
            }
            .registeration textarea{
                min-height: 92px;
                resize: vertical;
            }
            .registeration input[type="checkbox"],
            .registeration input[type="radio"]{
                display:inline-block;
                width:auto;
                margin-right:6px;
            }
            .registeration .inline-group{
                margin-top: 6px;
                line-height: 1.8;
            }
            .registeration button{
                margin-top: 16px;
                padding: 10px 14px;
                border: 0;
                border-radius: 8px;
                cursor: pointer;
                font: inherit;
            }
            .registeration button[type="submit"]{
                background: #2f4f4f;
                color: #ffffff;
            }
            .registeration button[type="reset"]{
                background: #e7e0d4;
                color: #2b2b2b;
                margin-left: 8px;
            }
        </style>
    </head>
    <body>
        <h1>Registration Form</h1>
        <div class="registeration">
        <form method="POST" enctype="multipart/form-data">
        <label for="name">Name</label>
        <input id="name" name="name" type="text" placeholder="Enter Name" required>
        <label for="email">Email</label>
        <input id="email" name="email" type="email" placeholder="Enter Mail ID" required>
        <label for="phone">Phone Number</label>
        <input id="phone" name="phone" type="tel" placeholder="Enter phone Number" pattern="[0-9]{10}" maxlength="10" required>
        <label for="photo">Photo Upload</label>
        <input id="photo" name="photo" type="file" accept="image/*">
        <label for="course">Course</label>
        <select id="course" name="course" required>
            <option value="">Select Course</option>
            <option value="bca">BCA</option>
            <option value="bba">BBA</option>
            <option value="bcom">B.Com</option>
            <option value="ba">BA</option>
        </select>
        <label for="address">Address</label>
        <textarea id="address" name="address" placeholder="Enter your address" required></textarea>
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
        <br>
        <button type="submit">Submit</button>
        <button type="reset">Reset</button>
        </form>
        </div>
    </body>
</html>