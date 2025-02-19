<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Mobile Design with Top Menu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        header {
            background-color: #FFF;
            color: #000000;
            text-align: center;
            padding: 1em;
            display: flex;
            justify-content: space-between; /* Align logo to the left and menu to the right */
            align-items: center; /* Center items vertically */
			border: 1px solid #ddd;
			border-top: 0px;
        }

        header img {
            max-width: 200px; /* Adjust this value based on your logo size */
            height: auto;
        }
		
        .container {
            max-width: 800px; /* Adjust this value based on your preference */
            margin: 0 auto; /* Center the container horizontally */
        }

        /* Global style for all a elements outside the navigation menu */
        a {
            color: black; /* Set text color to black */
            text-decoration: none;
        }

        main {
            padding: 1em;
            padding-bottom: 80px; /* Adjust this value based on your footer height */
        }

        .box {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 1em;
            margin-bottom: 1em;
        }

        h2 {
            color: #333;
            position: relative;
            padding-left: 15px;
            border: 0px solid black;
        }
        h2 img {
            max-width: 30px; /* Adjust this value as needed */
            height: auto;
            margin-right: 10px; /* Adjust this value as needed */
            margin-bottom: 10px;
            vertical-align: middle; /* Align with the text */
        }

        footer {
            background-color: #cccccc;
            color: #fff;
            text-align: center;
            padding: 1em;
            position: fixed;
            bottom: 0;
            width: 100%;
			border-top: 1px solid gray;
        }





* {
  font-family: "Raleway";
  box-sizing: border-box;
}

.top-nav {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  background-color: #00BAF0;
  background: linear-gradient(to left, #f46b45, #eea849);
  /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
  color: #FFF;
  height: 50px;
  padding: 1em;
}

.menu {
  display: flex;
  flex-direction: row;
  list-style-type: none;
  margin: 0;
  padding: 0;
}

.menu > li {
  margin: 0 1rem;
  overflow: hidden;
}

.menu-button-container {
  display: none;
  height: 100%;
  width: 30px;
  cursor: pointer;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

#menu-toggle {
  display: none;
}

.menu-button,
.menu-button::before,
.menu-button::after {
  display: block;
  background-color: #fff;
  position: absolute;
  height: 4px;
  width: 30px;
  transition: transform 400ms cubic-bezier(0.23, 1, 0.32, 1);
  border-radius: 2px;
}

.menu-button::before {
  content: '';
  margin-top: -8px;
}

.menu-button::after {
  content: '';
  margin-top: 8px;
}

#menu-toggle:checked + .menu-button-container .menu-button::before {
  margin-top: 0px;
  transform: rotate(405deg);
}

#menu-toggle:checked + .menu-button-container .menu-button {
  background: rgba(255, 255, 255, 0);
}

#menu-toggle:checked + .menu-button-container .menu-button::after {
  margin-top: 0px;
  transform: rotate(-405deg);
}

@media (max-width: 700px) {
  .menu-button-container {
    display: flex;
  }
  .menu {
    position: absolute;
    top: 0;
    margin-top: 50px;
    left: 0;
    flex-direction: column;
    width: 100%;
    justify-content: center;
    align-items: center;
  }
  #menu-toggle ~ .menu li {
    height: 0;
    margin: 0;
    padding: 0;
    border: 0;
    transition: height 400ms cubic-bezier(0.23, 1, 0.32, 1);
  }
  #menu-toggle:checked ~ .menu li {
    border: 1px solid #333;
    height: 2.5em;
    padding: 0.5em;
    transition: height 400ms cubic-bezier(0.23, 1, 0.32, 1);
  }
  .menu > li {
    display: flex;
    justify-content: center;
    margin: 0;
    padding: 0.5em 0;
    width: 100%;
    color: white;
    background-color: #222;
  }
  .menu > li:not(:last-child) {
    border-bottom: 1px solid #444;
  }
}
    </style>
</head>
<body>
<div class="container">
	<section class="top-nav">
		<div>
		  Lateweb.Info
		</div>
		<input id="menu-toggle" type="checkbox" />
		<label class='menu-button-container' for="menu-toggle">
		<div class='menu-button'></div>
	  </label>
		<ul class="menu">
		  <li>One</li>
		  <li>Two</li>
		  <li>Three</li>
		</ul>
	</section>
    <header>
		<img src="/forums2/template/images/logo.png" alt="Logo">
        <img src="https://archive.org/download/download-button-png/download-button-png.png" alt="Logo">
    </header> 

    <main>
        <div class="box">
            <h2><img src="template/images/document.png" alt="Document Icon"><a href="#">Section 1 this is going to be a long example header</a></h2>
            <div id="forum">
                <div id="forum-operations" style="background: #f4f4f4; padding: 8px; border: 1px dashed gray;">Общо Теми ( <b>2</b> ) Общо коментари ( <b>4</b> )<hr /><a href="operations/del_cat.php?cat_id=52" onclick="return confirm('Are you sure you want to delete this category?')">[ Изтрии ]</a> <a href="operations/edit_cat.php?cat_id=52">[ Редактирай ]</a> <a href="operations/movecat.php?cat_id=52&&action=up">[ ↑ ]</a> <a href="operations/movecat.php?cat_id=52&&action=down">[ ↓ ]</a></div>
                <div id="forum-desc"><p>Prism does its best to encourage good authoring practices. Therefore, it only works with rism does its best to encourage good authoring practices. Therefore, it only works with</p></div>
            </div>
        </div>

        <div class="box">
            <h2><img src="template/images/forum.png" alt="Document Icon"> Section 2</h2>
            <div id="forum">
                <div id="forum-operations" style="background: #f4f4f4; padding: 8px; border: 1px dashed gray;">Общо Теми ( <b>2</b> ) Общо коментари ( <b>4</b> )<hr /><a href="operations/del_cat.php?cat_id=52" onclick="return confirm('Are you sure you want to delete this category?')">[ Изтрии ]</a> <a href="operations/edit_cat.php?cat_id=52">[ Редактирай ]</a> <a href="operations/movecat.php?cat_id=52&&action=up">[ ↑ ]</a> <a href="operations/movecat.php?cat_id=52&&action=down">[ ↓ ]</a></div>
                <div id="forum-desc"><p>Prism does its best to encourage good authoring practices. Therefore, it only works with rism does its best to encourage good authoring practices. Therefore, it only works with</p></div>
            </div>
        </div>
        
        <div class="box">
            <h2><img src="template/images/php.png" alt="Document Icon"> Section 3</h2>
            <div id="forum">
                <div id="forum-operations" style="background: #f4f4f4; padding: 8px; border: 1px dashed gray;">Общо Теми ( <b>2</b> ) Общо коментари ( <b>4</b> )</div>
                <div id="forum-desc"><p>Prism does its best to encourage good authoring practices. Therefore, it only works with rism does its best to encourage good authoring practices. Therefore, it only works with</p></div>
            </div>
        </div>
    </main>
</div>
    <footer>
        <p>© 2023 Lateweb.info</p>
    </footer>

</body>
</html>
