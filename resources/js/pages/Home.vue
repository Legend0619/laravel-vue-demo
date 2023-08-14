<template>
    <div class="w-6/12 p-10 mx-auto">
        <div class="flex justify-between mb-4">
            <h1 class="text-2xl">History</h1>
            <span class="capitalize"
                >Welcome {{ user && user.name }},
                <button
                    class="text-orange-500 underline hover:no-underline rounded-md"
                    @click="handleLogout"
                >
                    Logout
                </button></span
            >
        </div>
        <table class="table table-striped mx-auto w-full text-center">
            <thead>
                <tr>
                    <th scope="col">Day</th>
                    <th scope="col">Login Time</th>
                    <th scope="col">Logout Time</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(record, idx) in history" :key="idx">
                    <td class="p-2">{{ record.day }}</td>
                    <td class="p-2">
                        <VueTimepicker
                            v-if="!record.checked"
                            format="HH:mm"
                            v-model="record.login"
                            :hideClearButton="true"
                        />
                        <span v-else>{{ record.logout }}</span>
                    </td>
                    <td class="p-2">
                        <VueTimepicker
                            v-if="!record.checked"
                            format="HH:mm"
                            v-model="record.logout"
                            :hideClearButton="true"
                            @onBlur="handleBlur(record, idx)"
                        />
                        <span v-else>{{ record.logout }}</span>
                    </td>
                    <td class="p-2">
                        <button
                            class="float-right bg-red-400 px-2 text-white font-bold rounded-md hover:bg-red-600"
                            @click="updateTime(record, idx)"
                            v-show="!record.checked"
                        >
                            Update
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { request, isEmpty } from "../helper";
import VueTimepicker from "vue3-timepicker";
import "vue3-timepicker/dist/VueTimepicker.css";

export default {
    components: {
        VueTimepicker,
    },
    setup() {
        const history = ref([]);
        const user = ref();
        const isLoading = ref();

        let router = useRouter();
        onMounted(() => {
            authentication();
            getHistory();
        });

        const authentication = async () => {
            isLoading.value = true;
            try {
                const req = await request("get", "/api/user");
                user.value = req.data;
            } catch (e) {
                await router.push("/");
            }
        };

        const getHistory = async () => {
            try {
                const req = await request("get", "/api/history");
                history.value = req.data;
            } catch (e) {
                await router.push("/");
            }
        };

        const handleLogout = () => {
            localStorage.removeItem("APP_DEMO_USER_TOKEN");
            router.push("/");
        };

        const timeToString = (time) => {
            if (isEmpty(time)) {
                let now = new Date();
                let hours = now.getHours();
                let minutes = now.getMinutes();
                let seconds = now.getSeconds();
                return `${hours.toString().padStart(2, "0")}:${minutes
                    .toString()
                    .padStart(2, "0")}:${seconds.toString().padStart(2, "0")}`;
            }
            return typeof time == "string" ? time : `${time.hh}:${time.mm}`;
        };

        const updateTime = async (record, idx) => {
            try {
                const login = timeToString(record.login);
                const logout = timeToString(record.logout);
                if (login >= logout) {
                    alert("logout time must later thant login time");
                    return;
                }
                const req = await request("post", "/api/update_time", {
                    id: record.id,
                    login,
                    logout,
                });
                if (req.data) alert(req.data);
                history.value[idx] = { ...record, checked: true };
            } catch (e) {
                await router.push("/");
            }
        };

        return {
            user,
            history,
            updateTime,
            timeToString,
            handleLogout,
        };
    },
};
</script>
