@if(core()->getConfigData('general.magic_ai.settings.enable') && core()->getConfigData('general.magic_ai.improver.enabled'))
    <x-admin::form.control-group.control
        type="textarea"
        :id="$attribute->code"
        :name="$attribute->code"
        :value="old($attribute->code) ?? $value"
        :rules="$validations"
        :label="$attribute->name"
        ai-enabled="true"
        rows="8"
    />
@else
    <x-admin::form.control-group.control
        type="textarea"
        :id="$attribute->code"
        :name="$attribute->code"
        :value="old($attribute->code) ?? $value"
        :rules="$validations"
        :label="$attribute->name"
    />
@endif