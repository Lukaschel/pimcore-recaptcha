# PimcoreRecaptcha

With this Pimcore bundle it is possible to integrate the Google ReCaptcha v3 logic into symfony forms.

## Installation

```json
"require" : {
    "lukaschel/pimcore-recaptcha" : "~1.0.0"
}
```

## Usage
After enabling and installing the bundle in the Pimcore backends, the bundle configuration can be used to set Recaptcha keys for each page.

## Form integration
Subsequently, a hidden input field can be deposited in the respective form:
```php
public function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder
        ->add('g_recaptcha_response', HiddenType::class, [
            'attr' => [
                'class' => 'g-recaptcha-response-input'
            ]
        ]);
}
```
Now you only have to validate the input field in your controller when your form is submitted:
```php
if ($form->isSubmitted() &&
    $form->isValid() &&
    $this->container->get('lukaschel.recaptcha')->validate($request->request->get('FORM_NAME')['g_recaptcha_response'])
    ) {
    ...
}

```

## Copyright and license
For licensing details please visit [LICENSE.md](LICENSE.md)
