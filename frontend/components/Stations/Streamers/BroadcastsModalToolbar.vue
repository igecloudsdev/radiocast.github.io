<template>
    <div class="card-body py-1 buttons d-flex align-items-center">
        <span>
            {{ $gettext('With selected:') }}
        </span>

        <button
            type="button"
            class="btn btn-sm btn-danger"
            :disabled="!hasSelectedItems"
            @click="doDelete"
        >
            <icon :icon="IconDelete" />
            <span>
                {{ $gettext('Delete') }}
            </span>
        </button>
    </div>
</template>

<script setup lang="ts">
import Icon from "~/components/Common/Icon.vue";
import {useTranslate} from "~/vendor/gettext";
import {useAxios} from "~/vendor/axios";
import {IconDelete} from "~/components/Common/icons";
import {computed, h, toRef, VNode} from "vue";
import {forEach, map} from "lodash";
import {useNotify} from "~/functions/useNotify.ts";
import {useDialog} from "~/functions/useDialog.ts";
import {HasRelistEmit} from "~/functions/useBaseEditModal.ts";
import {ApiGenericBatchResult} from "~/entities/ApiInterfaces.ts";

const props = defineProps<{
    batchUrl: string,
    selectedItems: Array<any>,
}>();

const emit = defineEmits<HasRelistEmit>();

const {$gettext} = useTranslate();
const {axios} = useAxios();

const selectedItems = toRef(props, 'selectedItems');

const hasSelectedItems = computed(() => {
    return selectedItems.value.length > 0;
});

type BatchAction = "delete";

const {notifySuccess, notifyError} = useNotify();

const handleBatchResponse = (
    data: ApiGenericBatchResult,
    successMessage: string,
    errorMessage: string
): void => {
    if (data.success) {
        const itemNameNodes: VNode[] = [];
        forEach(data.records, (item) => {
            itemNameNodes.push(h('div', {}, item.title));
        });

        notifySuccess(itemNameNodes, {
            title: successMessage
        });
    } else {
        const itemErrorNodes: VNode[] = [];
        forEach(data.errors, (err) => {
            itemErrorNodes.push(h('div', {}, err));
        })

        notifyError(itemErrorNodes, {
            title: errorMessage
        });
    }
}

const doBatch = (
    action: BatchAction,
    successMessage: string,
    errorMessage: string
) => {
    void axios.put<ApiGenericBatchResult>(props.batchUrl, {
        'do': action,
        'rows': map(props.selectedItems, 'id')
    }).then(({data}) => {
        handleBatchResponse(data, successMessage, errorMessage);
        emit('relist');
    });
};

const {confirmDelete} = useDialog();

const doDelete = () => {
    void confirmDelete({
        title: $gettext(
            'Delete %{num} broadcasts?',
            {
                num: String(props.selectedItems.length)
            }
        ),
    }).then((result) => {
        if (result.value) {
            doBatch(
                'delete',
                $gettext('Broadcasts removed:'),
                $gettext('Error removing broadcasts:')
            );
        }
    });
};
</script>
