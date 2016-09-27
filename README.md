# Bonnier Publications - WordPress cXense plugin

This plugin enables your WordPress site to integrate with cXense.
It adds the meta tags and scripts needed by cXense in order to scan your site.
It also calls cXense whenever you make changes to your content.
Finally it adds the possibility to save a cXense recommendation widget per content type and provides hooks to call them in your view/theme.
### Requirements

- WordPress 4.3 or higher
- Language support (Optional) Polylang plugin must installed and activated version 1.8.4 or higher
- PHP 5.6 or higher

### Installation/Configuration

Install through composer:

``` bash
composer require benjaminmedia/wp-cxense
```

Download lastest release from: https://github.com/BenjaminMedia/wp-cxense/releases
And unzip and place in your /wp-content/plugins directory.

#### Settings

Once you have installed and activated the plugin then make sure that you have
a set of api credentials for the cXense API.
Once you have your credentials you may go to the settings page labeled cXense.
Here you must enter your ```cXense Site ID```, ```cXense API user```,
 ```cXense API key``` and finally your ```cXense Organisation prefix```.

 ##### Remember the plugin will not work until you check the ```Enable``` switch in the settings page

---

### Widgets

The plugin will generate a ```CX Widget ID``` settings field for each post type
you have available on your WordPress Installation. Here you must enter the
cXense recommendation widget ID that you would like represented on your
content type.

#### Rendering widgets

In order to render the widgets available for your post you should make sure
that you have filled out the ```CX widget ID``` field in the plugin settings
page.

When rendering the widgets the plugin provides you with two options:

1. Let cXense render the HTML of the widgets
2. Get the data and for the widget and build the HMTL yourself

Once you have picked the method you want to use you should go to the theme file
that represents the single view of your post_type from this file you may call the
following methods to get the widget data:

###### Let cXense render the HTML of the widgets

``` php

wp_cxense()->render_widget(500, 600);

```

The sample output of this function could look like:

``` html

<div id="targetElement">
        <script type="text/javascript">
            document.write(data.response.template + '\n');
        </script>

    <div class="item">
        <a id="cXLinkIditl9abmrwgnm6p94" href="http://api.cxense.com/public/widget/click/GHVxM-yuEld7tlHB8lLrWC5jLoQYrF5gUs1rQK6CLuzqTVYiNXv6oZIhhNasqiBbPYlY2_nBmMn2aM8imnN5Y0VXxers9cYtaPN1-kIKDNd-dw78wcSKMCLNK45PnASBJagtCbsrhK9JwUfBEeuVtGs1L3HeqDWe5qKvKc7dWeFC77wB_w5fBFQlcXCgyOLmfQwO21Cfh2QcoBs4J8A2pN6OkKQOPyC0lwIbM30ZaHAUZ774R5ASnLc6GLOpPyN38YwdvjAL7iAigpE4fpAcq6O5HXG6PbQljmp-wk1GJvw6hWAigKp1gsSJmhPhZjhIP-pbABDfOl7OkS-k42wjAxKuCeukqgJW04_J0tWRGygNzTm4Tno54OQAgHVWnh4iZDyOaXBjkKAGQHDMPY7IGBfSjDZKOiviklKINjAbb1STXbgDr27BhMZKuWC-y4fMWVnganRI7IVRA8bgd6zl0V4DX9VjvTUEJLSOWq-T6kEzsdG-ENGrw4EVauuyd1h9kysOAuH_egm1XoDcmHpYSMzPmhmyeOgfJpGFg6L09vXFTi6GQ39q4kJYMeQSlC_xO3f1vJzIhn9Y9D6kRIVQMASyFC31lcSwR9pVoOcQav5duA-d4wVciKTKq_RehjVfwqEFxYz9v0WTo8Sqcoc0" target="_top">
            <div class="thumbnail" style="width: 300px; height: 200px;  overflow: hidden; position: relative;"><img style="position: relative;   left: 0px;   top: -7.2px;" src="http://content-thumbnail.cxpublic.com/content/dominantthumbnail/f95381682e3604de99d30788b24fb3a1538616ee.jpg?57e79849" width="300" height="236" alt=""></div>

            <div class="text-wrapper">
                <h3 class="title">Hindbærsmoothie med æble og lakrids</h3>

            </div>
        </a>
    </div>



    <div class="item">
        <a id="cXLinkIditl9abmrpj1kla6v" href="http://maaltid.nu/herkules-pavillonens-bedste-frokost-opskrifter" target="_top">
            <div class="thumbnail" style="width: 300px; height: 200px;  overflow: hidden; position: relative;"><img style="position: relative;   left: 0px;   top: -7.2px;" src="http://content-thumbnail.cxpublic.com/content/dominantthumbnail/cf4b03ff330434ddec3447a014ccbd4a15089290.jpg?57d6c1e2" width="300" height="236" alt=""></div>

            <div class="text-wrapper">
                <h3 class="title">Herkules Pavillonens bedste frokost-opskrifter</h3>

            </div>
        </a>
    </div>

	...

</div>

```
---

###### Get the data and for the widget and build the HMTL yourself

``` php

wp_cxense()->get_widget_data();

```

The sample output of this function could look like:


``` php

array (size=12)
  0 =>
    object(stdClass)[2636]
      public 'recs-articleid' => string '795' (length=3)
      public 'bod-pagetype' => string 'recipe' (length=6)
      public 'dominantthumbnail' => string 'http://content-thumbnail.cxpublic.com/content/dominantthumbnail/f95381682e3604de99d30788b24fb3a1538616ee.jpg?57e79849' (length=117)
      public 'dominantthumbnaildimensions' => string '300x236' (length=7)
      public 'title' => string 'Hindbærsmoothie med æble og lakrids' (length=37)
      public 'click_url' => string 'http://api.cxense.com/public/widget/click/HV4bL7qxuVIVUmrMEsh4zQ6S2A5_BkkxEuByIzyDKMZAYCrJlJOTpy7QFvYxf7dkW0ljBE_55ig9vYxG3FNaXmKGsr03HH5w1wa7-E6mbJI-gdqHJNx6pgIjvKyM8TUGnhfwe0NsyjAkgzirNM66WfPhG9_Z9lsiWIJauO3C82KWYdRhA99EBXg6Ynsh9xwIq62wNJAaj_OPTVFhUuGiLxW7uVQ4DNT0BjRcn8ne0Uq6y8Ew0DYUDCfCFp6DwOy_D9MK7j5wpKSfRykLOGNNo35udNcfALBcZJfoMZ7Uqgs19IZeGrAv11RDIz1cNBmWq3yEn-QKqeKFgK7cqwjKeCr91PERbwGbDu4kN9xkv1NPjTK1M_attpQjlCLy3E9_JstO-aCxP6XtmtnIExUnl1Qq7iVPXAbP3fxlT3wmIOetk4IvoGKsXhdFkRIMCD1owQwY27bVvzwuPCgTXJ-7UO'... (length=599)
      public 'url' => string 'http://maaltid.nu/opskrifter/hindbaersmoothie-med-aeble-og-lakrids' (length=66)
  1 =>
    object(stdClass)[2672]
      public 'recs-articleid' => string '1369' (length=4)
      public 'bod-pagetype' => string 'recipe' (length=6)
      public 'dominantthumbnail' => string 'http://content-thumbnail.cxpublic.com/content/dominantthumbnail/6b0ddce458039f3905b5b3515356002536ab614c.jpg?57ea261a' (length=117)
      public 'dominantthumbnaildimensions' => string '300x236' (length=7)
      public 'title' => string 'Italiensk til middag: Risotto med 2 slags græskar og salvie' (length=60)
      public 'click_url' => string 'http://api.cxense.com/public/widget/click/mLsD8MoXunkEcOtky6wQJXpRjzYcvvrFoXsONQdOI5FDqsmnlun6sVcaK-PIsvyT9KoXSvOKxiS4wzBrjGb0PaCtk_1bFn88sHSzq7LJF4CL7-Q4-50nJl3JC3oyrnAM1W_Cub7qXcAHSFmBjVkBjAGRaiA_F-Du9Y1q2by9YikilWco43hRZeUHVyQ2RLOZ7as8C1SeEXdKHeDi8HT5SGqMox-hAdBhwHNLtXLdJ_t1u9G90QOmQw-xitTc4PUknHSdtPyiDgYrBNNxHhOehs0_wHKGxka4k-g6VOAo7ovFGF2aRB-32QAqSPu_ohd-nNO_J0tEMll1uofTI9XCePnkI2O1W0Cq6MkEcyWVZQ-72hWiTrfn2z8x-BRd9ZE8cFvdWV8OU-Ojf02KEKesGfVgUltFY-HLImdE2NCkGV-jk7RoHYXesghIvfTufvKnMORmayI5lkLDZQ5Dp7u9wh'... (length=613)
      public 'url' => string 'http://maaltid.nu/opskrifter/kom-i-godt-humor-med-graeskarrisotto-med-salvie' (length=76)
  2 => ...

```
