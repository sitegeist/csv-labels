# CSV Translation Files for TYPO3

This utility extension extends the language handling of TYPO3 to be able to use a simple
CSV file format to specify language labels. While the XLIFF file format is a widely used
standard and is more powerful, CSV files can be a pragmatic approach for language files that
only contain a handful of entries.

This extension **does not** remove or disable XLIFF files, it merely provides another file
format to choose from. We at sitegeist use the CSV file format to define language labels for
[Fluid Components](https://github.com/sitegeist/fluid-components).

## Getting started

Install the extension either [from TER](https://extensions.typo3.org/extension/csv_labels/) or [via composer](https://packagist.org/packages/sitegeist/csv-labels):

```
composer require sitegeist/csv-labels
```

## File format

**locallang.csv:**

```
"id","description","default","de"
"Actions","","Actions","Aktionen"
"ConfirmDelete","Message in the delete user dialog","Should the following user be deleted?","Soll der folgende Nutzer gelöscht werden?"
```

Rules:

* The column `id` represents the label identifier
* The column `description` represents the label description (for documentation purposes)
* All other columns are treated as a translation for the locale from the header line
* The csv files use `,` as delimiter and `"` as text delimiters (default configuration of
[fgetcsv](https://www.php.net/manual/en/function.fgetcsv.php))

## Usage

```xml
<!-- either explicitly -->
<f:translate key="EXT:my_extension/Resources/Private/Language/locallang.csv:Actions" />
<!-- or as a fallback, in case the xlf file doesn't exist -->
<f:translate key="EXT:my_extension/Resources/Private/Language/locallang.xlf:Actions" />
<!-- this means that it can also be used in extbase context without the full path -->
<f:translate key="Actions" />
```

## Authors & Sponsors

* Simon Praetorius - praetorius@sitegeist.de
* [All contributors](https://github.com/sitegeist/fluid-components/graphs/contributors)

*The development and the public-releases of this package is generously sponsored
by my employer https://sitegeist.de.*
