<template>
    <modal-form
        ref="$modal"
        :loading="loading"
        :title="langTitle"
        :error="error"
        :disable-save-button="v$.$invalid"
        @submit="doSubmit"
        @hidden="clearContents"
    >
        <admin-custom-fields-form
            v-model:form="form"
            :auto-assign-types="autoAssignTypes"
        />
    </modal-form>
</template>

<script setup lang="ts">
import ModalForm from "~/components/Common/ModalForm.vue";
import AdminCustomFieldsForm from "~/components/Admin/CustomFields/Form.vue";
import {computed, useTemplateRef} from "vue";
import {BaseEditModalEmits, BaseEditModalProps, useBaseEditModal} from "~/functions/useBaseEditModal";
import {useTranslate} from "~/vendor/gettext";
import {CustomField} from "~/entities/ApiInterfaces.ts";

interface CustomFieldsEditModalProps extends BaseEditModalProps {
    autoAssignTypes: Record<string, string>
}

const props = defineProps<CustomFieldsEditModalProps>();
const emit = defineEmits<BaseEditModalEmits>();

const $modal = useTemplateRef('$modal');

const {
    loading,
    error,
    isEditMode,
    form,
    v$,
    clearContents,
    create,
    edit,
    doSubmit,
    close
} = useBaseEditModal<CustomField>(
    props,
    emit,
    $modal
);

const {$gettext} = useTranslate();

const langTitle = computed(() => {
    return isEditMode.value
        ? $gettext('Edit Custom Field')
        : $gettext('Add Custom Field');
});

defineExpose({
    create,
    edit,
    close
});
</script>
