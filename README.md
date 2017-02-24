
**Upload plugin for CakePHP 3.x**
=============================
By [Allan Carvalho](https://www.facebook.com/Allan.Mariucci.Carvalho)
---------------------------------------------------------------------

# **Installation**

### 1. Installing dependency
You can install this plugin into your CakePHP application using [`composer`](http://getcomposer.org).
The recommended way to install composer packages is:
```bacth
composer require allanmcarvalho/upload
```

### 2. Loading plugin

In `App\config\bootstrap.php`
```php
Plugin::load('Upload', ['bootstrap' => true]);
```

# **Basic usage**

#### **1** - You should open the table and then add the `behavior` of the plugin **Upload**.


```php
// in App\Model\Table\ExamplesTable.php
class ExamplesTable extends Table
{
...
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->addBehavior('Upload.Upload', [
			'file1' => [],
			'file2' => []
		]);
    }
...
```
> **Note:**
> Just adding the `Upload.Upload` **Behavior** does not mean it's ready, in fact, if you just add, nothing will happen. You must define which table columns are going to be responsible for storing the file name as shown above (`file1` and `file2`).

####**2** - Now should open the view from the form and configure the `form` and `input` to be of the file type.

```php
// in App\Template\Controller\add.ctp
...
	<?= $this->Form->create($example, ['type' => 'file']) ?>
	<?= $this->Form->control('title') ?>
	<?= $this->Form->control('file1', ['type' => 'file']) ?>
	<?= $this->Form->control('file2', ['type' => 'file']) ?>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
...
```

### Available **Behavior** settings

 - **path:** 
 
```php
// in App\Model\Table\ExamplesTable.php
	
	$this->addBehavior('Upload.Upload', [
		'file1' => [
			'path' => WWW_ROOT . 'img' . DS,
			'prefix' => 'example_'
		]
	]);
```
> **Options**
>  
> - **path:** The path where that file will be saved. 

>> **Note:**
>> When `path` is not provided, the default is `WWW_ROOT . 'files' . DS . $this->table->getAlias() . DS` or `WWW_ROOT . 'img' . DS . $this->table->getAlias() . DS` when the `image` setting is set.
>
> - **prefix:** A prefix to be added to the image file name.  Default: **Does not have**;


----------


 - **image:** Set of settings for image uploads;
 
```php
// in App\Model\Table\ExamplesTable.php
	
	$this->addBehavior('Upload.Upload', [
		'file1'  => [
			'image'  => [
				'format'     => 'jpg',
				'quality'    => 75,
				'watermark'  => WWW_ROOT . 'img' . DS . 'watermark.png',
				'watermark_position' => 'bottom-right'
				'resize' => [
					'width' => 500,
					'height' => 600,
				],
				'crop' => [
					'width' => 400,
					'height' => 400,
				],
				'thumbnails' => [
					[
						'width'  => 450,
						'height' => 400,
						'crop' => [
							'width' => 400,
							'height' => 400,
						],
					],
					[
						'width'     => 225,
						'height'    => 200,
						'watermark' => false;
					]
				]
			]
		]
	]);
```
> **Image options:**
> 
>  - **crop:** (optional)  Crop the image. Default: **Does not have**;
	 - **width:** (required) The crop image width. Default: **Does not have**;
	 - **height:** (required) The crop image height. Default:  **Does not have**;
	 - **x:** (required) The crop image x position. Default:  **Center**;
	 - **y:** (required) The crop image y position. Default:  **Center**;
 - **format:** Image format. It can be (jpg, png, gif). Default: `jpg`;
 - **quality:** Image quality from 1 to 100. Default: `100`;
 - **resize:** (optional)  Changes the image size. Default: **Does not have**;
	 - **width:** (optional) New image width. Default: **If height is set is automatic**;
	 - **height:** (optional) New image height. Default: **If width is set is automatic**;
 - **thumbnails:** (optional) Setting to set thumbnails to be created. Default: **Does not have**;
	 - **width:** (required) Thumbnail width. Default: **Does not have**;
	 - **height:** (required) Thumbnail height. Default: **Does not have**;
	 - **watermark:** (optional) Sets whether the thumbnail will have the same watermark as the original image (if the original has). Default: `true`;
	 - **crop:** (optional) Crop the new thumbnail image. Default: **Does not have**;
		 - **width:** (required) New image crop width. Default:**Does not have**;
		 - **height:** (required) New image height. Default: **Does not have**;
		 - **x:** (required) The crop image x position. Default:  **Center**;
	 	 - **y:** (required) The crop image y position. Default:  **Center**;
 - **watermark:** (optional) Watermak full file path. Default: **Does not have**;
 - **watermark_position:** (optional) Watermak orientation. Default: `bottom-right`. It can be:
	 - **top-left**
	 - **top**
	 - **top-right**
	 - **left**
	 - **center**
	 - **right**
	 - **bottom-left**
	 - **bottom**
	 - **bottom-right**


License


----------


MIT
