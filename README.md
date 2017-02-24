
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


### Available **Behavior** settings

> **Note:** The configuration name must be the same as the table column name. In this example is `file1`.
 
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
				'crop' => [
					'width' => 600 // Height will be the same
				],
				'format' => 'png',
				'quality' => 75, 
				'resize' => [
					'height' => 750, // width will be automatically calculated
				],
				'thumbnails' => [
					[
						'height' => 750, // width will be automatically calculated
						'watermark' => false // Disables watermark for this item
					],
					[
						'height' => 750,
						'width' => 750,
						'watermark' => [
							'opacity' => 60, // 60% of opacity
							'position' => 'top', // center top position
							'path' => WWW_ROOT . 'img' . DS . 'watermark2.png',
						],
						'crop' => [
							'width' => 600 // Height will be the same 
						]
					]
				],
				'watermark' => [
					'ignore_default' => true, //do not insert watermark on default
					'opacity' => 10, // 10% of opacity
					'path' => WWW_ROOT . 'img' . DS . 'watermark.png',
					'position' => 'bottom-right'
				]
			]
		]
	]);
```
> **Image options:**
>  > **Note:** Compatibility formats to use these settings: `jpg`, `png` and `gif`.
>  
>  - **crop:** (optional)  Crop the image. **Obs.:** If resize is also configured, it will be done before crop. Default: **Does not have**;
	 - **width:** (at least one) The crop image width. Default: **If height is set is the same**;
	 - **height:** (at least one) The crop image height. Default:  **If widthis set is the same**;
	 - **x:** (optional) The crop image x position based from left. Default:  **Center**;
	 - **y:** (optional) The crop image y position based from top. Default:  **Center**;
 - **format:** Image format. It can be (jpg, png, gif). Default: `jpg`;
 - **quality:** Image quality from 1 to 100. Default: `100`;
 - **resize:** (optional)  Changes the image size. Default: **Does not have**;
	 - **width:** (at least one) New image width. Default: **If height is set is automatic**;
	 - **height:** (at least one) New image height. Default: **If width is set is automatic**;
 - **thumbnails:** (optional) Setting to set thumbnails to be created. Default: **Does not have**;
	 - **width:** (at least one) Thumbnail width. Default: **If height is set is automatic**;
	 - **height:** (at least one) Thumbnail height. Default: **If width is set is automatic**;
	 - **watermark:** (optional) If `true` follows the default image settings (if exists). If `false` does not insert the watermark. If any setting is passed in an **array**, overwrites the default image settings. Default: `true`;
		 - **opacity:** (optional) Watermak opacity from 1 to 100 where the smaller is more transparent. Default:  **Same as original**.
		 - **path:** (optional) Path to watermark image for this thumbnail. Default: **Same as original**;
		 - **position:** (optional) Watermak orientation. Default: `bottom-right`. It can be the same positions quotes below;
	 - **crop:** (optional) Crop the new thumbnail image. **Obs.:** If resize is also configured, it will be done before crop. Default: **Does not have**;
		 - **width:** (required) New image crop width. Default:**Does not have**;
		 - **height:** (required) New image height. Default: **Does not have**;
		 - **x:** (required) The crop image x position. Default:  **Center**;
	 	 - **y:** (required) The crop image y position. Default:  **Center**;
 - **watermark:** Insert watermark on image. Default: **Does not have**;
	- **ignore_default:** (optional) If `true` ignores the watermark in the default image. Default: `false`;
	- **opacity:** (optional) Watermak opacity from 1 to 100 where the smaller is more transparent. Default:  `100`.
	- **path:** (required) Path to watermark image. Default:**Does not have**;
	- **position:** (optional) Watermak orientation. Default: `bottom-right`. It can be:
		- **top-left**
		- **top**
		- **top-right**
		- **left**
		- **center**
		- **right**
		- **bottom-left**
		- **bottom**
		- **bottom-right**
 

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

####**3** - Deleting files without deleting entity

```php
// in App\Controller\ExampleController.php
...
public function deleteFiles($id)
{
	$this->request->allowMethod(['post', 'delete']);
	$example = $this->Examples->get($id);
	if ($this->Examples->deleteFiles($example)) {
		$this->Flash->success(__('The files has been deleted.'));
	} else {
		$this->Flash->error(__('The files could not be deleted. Please, try again.'));
	}
	return $this->redirect(['action' => 'index']);
}
...
```

or

```php
// in App\Controller\ExampleController.php
...
public function deleteFiles($id)
{
	$this->request->allowMethod(['post', 'delete']);
	$example = $this->Examples->get($id);
	if ($this->Examples->deleteFiles($example, ['file1'])) {
		$this->Flash->success(__('The files has been deleted.'));
	} else {
		$this->Flash->error(__('The files could not be deleted. Please, try again.'));
	}
	return $this->redirect(['action' => 'index']);
}
...
```

> **Note:** The `deleteFiles($entity, $fields = [])` method is a table method added by behavior and you can even use inside the **table** class.


####**4** - Validations

There are two types of validators, one to validate information of the files called `UploadValidation`, and one that after additional features to validate images called  `ImageValidation`. You can also use the two in one by calling `DefaultValidation`.

```php
// in App/Model/Table/ExampleTable.php
// Contain files validations
	public function validationDefault(Validator $validator)
	{
		$validator->setProvider('upload', \Upload\Validation\UploadValidation::class);

		$validator
			->add('file1', 'isUnderPhpSizeLimit', [
				'rule' => 'isUnderPhpSizeLimit', 
				'message' => 'Must have a wider width',
				'provider' => 'upload'
			]);
		
		$validator
			->add('file1', 'isUnderFormSizeLimit', [
				'rule' => 'isUnderFormSizeLimit',
				'message' => 'Must have the shortest width',
				'provider' => 'upload'
			]);
			
		$validator
			->add('file1', 'isCompletedUpload', [
				'rule' => 'isCompletedUpload',
				'message' => 'Must have a wider height',
				'provider' => 'upload'
			]);
			
		$validator
			->add('file1', 'isFileUpload', [
				'rule' => 'isFileUpload',
				'message' => 'Must have the shortest height',
				'provider' => 'upload'
			]);
			
		$validator
			->add('file1', 'isSuccessfulWrite', [
				'rule' => 'isSuccessfulWrite',
				'message' => 'Wrong aspect ratio',
				'provider' => 'upload'
			]);
			
		$validator
			->add('file1', 'isAboveMinSize', [
				'rule' => ['isAboveMinSize', 2048],
				'message' => 'Wrong image extension',
				'provider' => 'upload'
			]);
		$validator
			->add('file1', 'isBelowMaxSize', [
				'rule' => ['isBelowMaxSize', 2048],
				'message' => 'Must have the shortest height',
				'provider' => 'upload'
			]);
			
		$validator
			->add('file1', 'isThisMimeType', [
				'rule' => ['isThisMimeType', ['image/jpeg', 'image/png']],
				'message' => 'Wrong aspect ratio',
				'provider' => 'upload'
			]);
	}
```

or
```php
// in App/Model/Table/ExampleTable.php
// Contain image validations
	public function validationDefault(Validator $validator)
	{
		$validator->setProvider('upload', \Upload\Validation\ImageValidation::class);

		$validator
			->add('file1', 'isAboveMinWidth', [
				'rule' => ['isAboveMinWidth', 100], 
				'message' => 'Must have a wider width',
				'provider' => 'upload'
			]);
		
		$validator
			->add('file1', 'isBelowMaxWidth', [
				'rule' => ['isBelowMaxWidth', 900],
				'message' => 'Must have the shortest width',
				'provider' => 'upload'
			]);
			
		$validator
			->add('file1', 'isAboveMinHeight', [
				'rule' => ['isAboveMinHeight', 100],
				'message' => 'Must have a wider height',
				'provider' => 'upload'
			]);
			
		$validator
			->add('file1', 'isBelowMaxHeight', [
				'rule' => ['isBelowMaxHeight', 900],
				'message' => 'Must have the shortest height',
				'provider' => 'upload'
			]);
			
		$validator
			->add('file1', 'isThisAspectRatio', [
				'rule' => ['isThisAspectRatio', 3, 4],
				'message' => 'Wrong aspect ratio',
				'provider' => 'upload'
			]);
			
		$validator
			->add('file1', 'isThisExtension', [
				'rule' => ['isThisExtension', ['jpg', 'png']],
				'message' => 'Wrong image extension',
				'provider' => 'upload'
			]);
			
	}
```
or
```php
// in App/Model/Table/ExampleTable.php
// Contains both validations
	public function validationDefault(Validator $validator)
	{
		$validator->setProvider('upload', \Upload\Validation\DefaultValidation::class);
		...
	}
```

####**License: [MIT](https://opensource.org/licenses/MIT)**


