<?php require_once('head.php') ?>

<body>
    <section class="section">
        <div class="container">
            <div class="columns is-mobile is-multiline">
                <div class="column is-half">
                    <h1 class="title is-1 is-spaced bd-anchor-title">WARNING</h1>
                    <p>You are going to: <strong><?=htmlentities($url) ?></strong></p>
                    <br />
                    <form method="POST">
                        <input type="hidden" name="go" value="1">
                        <input class="button is-success" type="submit" value="Go" />
                        <input class="button is-danger" type="button" value="Leave" onclick="history.back();" />
                    </form>
                </div>
            </div>
        </div>
    </section>
    <section class="section">
        <div class="container">
            <h3 class="title is-3 is-spaced bd-anchor-title">Preview screenshot:</h3>
            <form method="POST">
                <input type="hidden" name="regen_screenshot" value="1">
                <button class="button is-warning" type="submit" onclick="">Regenerate screenshot</button>
            </form>
            <div class='card-content is-flex is-horizontal-center'>
                <figure class='image'>
                    <img style="border: 5px #aaa outset" src="<?=htmlentities($screenshot, ENT_QUOTES) ?>" />
                </figure>
            </div>
        </div>
    </section>
</body>