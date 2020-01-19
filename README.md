# Lofi

Lofi is a tiny CMS that lets you string together content from various [txti.es](http://txti.es) and use them to make a simple website. It uses the wonderfully minimal [Water.css](https://watercss.netlify.com/) for styling.

## Content and configuration

First you need to create some content. You will be creating several txti pages:

- One for your site header (optional)
- One for your site footer (optional)
- One for each page of your website (at least 1 required)

To make your life easier, I recommend setting a custom edit code when you are creating your txti and re-using it for each page. I would also recommend using custom URLs for your txties just so it's easy for you to find them.

Lofi is configured using a JSON file. You will create this file in the root of your website. It must be called `lofi-data.json`. Here is an example configuration file:

```
{
    "title": "My Site",
    "header": "mysite-header",
    "footer": "mysite-footer",
    "pages": [
        { "title": "Home", "path": "/", "txti": "mysite-home" },
        { "title": "About", "path": "/about", "txti": "mysite-about" }
    ]
}
```


The `title` will be displayed in the web browser tab and in search results. The `header` and `footer` are optional but, if included, can be set to txti URL paths, which will be used to display content at the top and bottom of every page of your site.

## Installation

Head on over to the [GitHub project page](https://github.com/blakewatson/lofi) and download the two files, `index.php` and `.htaccess`. You should be able to drop those files in the web root on just about any PHP-enabled Apache server. You also need to put the `lofi-data.json` file you created in the last step in the web root. That's it, you should see your website when you visit the URL!

## Features

**Navigation menu** - by default, Lofi will create a navigation menu made up of all your pages, listed in the order they are given in `lofi-data.json`. You can display the menu horizontally by setting `menu` to `horizontal`. You can hide the menu altogether by setting `menu` to `false` -- useful should you want to provide your own menu in the header.

**Code snippets** - txti code snippets turn special HTML characters (like `"`) into [HTML entities](https://developer.mozilla.org/en-US/docs/Glossary/Entity). Lofi will decode these entities inside of `<code>` tags so that code snippets are properly displayed.