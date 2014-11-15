Documentation
=========

Contents can be written in HTML and [Markdown](http://daringfireball.net/projects/markdown/basics).

Build and serve documentation locally
---------------------------------

The documentation is hosted using GitHub Pages.
You can build and serve documentation locally using Jekyll.
Please see [GitHub's help on how to use Jekyll with Pages](https://help.github.com/articles/using-jekyll-with-pages).

As some URLs within the tutorials require the full hostname, you may encounter situations where you will not see local modifications, because the generated site still refers to a remote resource/a remote URL.
For that reason we included a local configuration that adjusts the URLs used to your local site.
Append `--config _config.yml,_config_local.yml` to your Jekyll command to override the remote URL setting.
It is important that `_config_local.yml` is provided as the second comma separated parameter!

Create new content
--------------------

### Create directory and content
To create Category, create a directory in `chapters`.
As the directory name is being used for the URL and other configurations, you should choose an established abbreviation.

Each chapter has to have an `index.md` file that describes the lecture and also serves as a landing page for the respective lecture.
The file has to start with the Liquid declaration of the chapter's full title and the document's layout (`chapter` will automatically generate table of contents/chapters).
The chapter's full title is used in the overview, the chapter's landing page, and at the top of each page.
```
---
chapter: Enterprise Application Integration
layout: chapter
---

Description of the chapter in *Markdown syntax*
```

Each tutorial is contained in a single file that you can name at your convenience.
Please note that tutorials within a chapter are sorted by their respective file names; therefore using numbers as prefixes allows you to control the order of appearance in the overview and table of contents (e.g. 010-introduction.md, 020-basics.md, etc).
A tutorial's content file also has to start with a Liquid declaration for the tutorial's title and its layout.
The title is used for the overview, the table of contents on the chapter's landing page, and as a heading for the specific tutorial.
```
---
title: Introduction to EAI
layout: recipe
---

Content of the tutorial, which could also be put into a html file.
```

### Update configuration
To make a chapter appear, you have to add information for `chapters` and `contacts` in `_config.yml`.

Writing a chapter
------------------

### Images
Besides Markdown's syntax to include images (i.e. `![alt text](/path/to/img.jpg "Title")`), we included a JavaScript based mechanism to provide more detailed captions for images.
Whenever an image is the first element within a paragraph and it is immediately followed by some text, the text is assumed to be captioned and displayed accordingly.

Example:
```
Some text…

![alt text](/path/to/img.jpg "Title")
This will be rendered as a caption.

This will not be part of the caption! This is a new paragraph.
```
