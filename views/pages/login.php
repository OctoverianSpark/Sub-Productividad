<main>



    <h1 class="title">INICIO DE SESION</h1>
    <div class="container-login">
        <picture class="login-logo">
            <source srcset="/build/img/logo.webp" type="image/webp">
            <img src="/build/img/logo.png" type="image/png" alt="Logo inicio de sesion">
        </picture>
        <form method="POST" class="inicio-sesion">
            <fieldset class="container-inputs">
                <a href="/redirect" class="boton-fucsia-inline"><i class='bx bxl-google'></i> Continuar con Google</a>
                <div class="divider-from-login">
                </div>

                <legend>DATOS DE INICIO DE SESION</legend>
                <div class="container-input">
                    <label for="user">USUARIO</label>
                    <input type="text" id="user" name="login[user]">

                </div>
                <div class="container-input">
                    <label for="password">CONTRASEÑA</label>
                    <input type="password" name="login[password]" id="password">

                </div>
                <input type="submit" value="Iniciar Sesion" class="boton-morado-block">
                <?php if (!empty($errorMessage)): ?>
                    <div class="error-message">
                        <?= htmlspecialchars($errorMessage) ?>
                    </div>
                <?php endif; ?>
            </fieldset>
        </form>
    </div>





</main>