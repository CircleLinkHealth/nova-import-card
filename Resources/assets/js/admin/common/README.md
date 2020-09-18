# Common Vue Components

## Modal

The Modal is a generic modal component. It depends on an EventBus component called `Event` which is obtained from `vue-tables-2` (Pls, feel free to replace this, if you have to)

### Modal Events

You can Trigger the `modal:show` event with:

```js
Event.$emit('modal:show', 
    { 
        title: 'My Modal Title', 
        body: 'Hello Modal Everyone', 
        footer: 'Cool Modal!' 
    })
```

You can hide the modal using:

```js
Event.$emit('modal:hide')
```

### Modal Props

```
name (string)
no-title (boolean)
no-footer (boolean)
info (object | array | ...)
class-name (string)
```

#### Name

You could pass a `name` prop to the modal to differentiate it from others.

When specify, the event trigger key to show the modal becomes `"modal-<name>:show"` where you replace `<name>` with whatever `:name` value you passed to the modal.

E.g.

To show this modal:

```html
<modal :name="'say-bye'"></modal>
```

You'd need:

```html
Event.$emit('modal-say-bye:show')
```

#### No-Title and No-Footer

You could choose to not show the modal title or footer by passing the `no-title` and `no-footer` props which take boolean values. e.g.

```html
<modal :no-title="true" :no-footer="true"></modal>
```

#### Info

The `info` prop is used to pass objects to the custom vue components existing within the modal. You may choose to render custom vue components within the modal, using the template slots explained below.

#### Class-Name

The `class-name` prop is used to specify a parent `[class]` for css styling the modal. E.g.

```html
<style>
    .my-custom-modal {
        width: 700px;
    }
</style>
```

```html
<modal :class-name="'my-custom-modal'"></modal>
```

#### Templates

You may specify the templates for modal title, body and footer. 

For Body,

```html
<template>
 ... body html here ... 
</template>
```

For title,

```html
<template slot='title'>
 ... title html here ... 
</template>
```

For footer,

```html
<template slot='footer'>
 ... footer html here ... 
</template>
```

If rendering custom vue components within the templates, you could pass the :info prop to the modal component.

Within the template slots, you can use scope to props e.g.

```html
<modal :no-title="true" :no-footer="true" :info="selectNursesModalInfo">
    <template scope="props">
        <select class="form-control" @change="props.info.onChange">
            <option value="">Pick a Nurse</option>
            <option value="1">Nurse N RN</option>
            <option value="2">Kathryn Alchalabi RN</option>
        </select>
    </template>
</modal>
```

Where [selectNursesModalInfo] is an object that contains the `onChange` callback e.g.

```js
export default {
    data() {
        selectNursesModalInfo: {
            onChange(e) {
                console.log(e)
            }
        }
    }
}
```