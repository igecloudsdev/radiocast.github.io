<template>
    <div class="outside-card-header d-flex align-items-center">
        <div
            v-if="station.listen_url && hasStarted"
            class="flex-shrink-0 me-2"
        >
            <play-button
                class="btn-xl"
                :url="station.listen_url"
                is-stream
            />
        </div>
        <div class="flex-fill">
            <h2 class="display-6 m-0">
                {{ stationName }}<br>
                <small
                    v-if="stationDescription"
                    class="text-muted"
                >
                    {{ stationDescription }}
                </small>
            </h2>
        </div>
        <div
            v-if="userAllowedForStation(StationPermissions.Profile)"
            class="flex-shrink-0 ms-3"
        >
            <router-link
                class="btn btn-primary"
                role="button"
                :to="{name: 'stations:profile:edit'}"
            >
                <icon :icon="IconEdit" />
                <span>
                    {{ $gettext('Edit Profile') }}
                </span>
            </router-link>
        </div>
    </div>
</template>

<script setup lang="ts">
import Icon from "~/components/Common/Icon.vue";
import PlayButton from "~/components/Common/PlayButton.vue";
import {userAllowedForStation} from "~/acl";
import {IconEdit} from "~/components/Common/icons";
import {ApiNowPlayingStation, StationPermissions} from "~/entities/ApiInterfaces.ts";

export interface ProfileHeaderPanelParentProps {
    stationName: string,
    stationDescription: string,
    hasStarted: boolean,
}

defineOptions({
    inheritAttrs: false
});

interface ProfileHeaderPanelProps extends ProfileHeaderPanelParentProps {
    station: ApiNowPlayingStation
}

defineProps<ProfileHeaderPanelProps>();
</script>
