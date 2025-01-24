<script setup>
import MagnifyingGlass from "@/Components/Icons/MagnifyingGlass.vue";
import Pagination from "@/Components/Pagination.vue";
import ConfirmDilogue from "@/Components/ConfirmDilogue.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {Head, Link, router, useForm, usePage} from "@inertiajs/vue3";
import {ref, watch, computed, onMounted} from "vue";
import Toast from "@/Components/Toast.vue";
import { TailwindPagination } from 'laravel-vue-pagination';


defineProps({
    students: {
        type: Object,
    },
    classes: {
        type: Object,
        required: true,
    },
    message: {
        type: String,
        required: true
    },
    totalPages: {
        type: Number,
        required: true
    },
    currentPage: {
        type: Number,
        required: true
    }
});

const isLoading = ref(true); // Loading state

onMounted(() => {
    // Simulate data fetching delay
    setTimeout(() => {
        isLoading.value = false; // Stop showing the skeleton loader
    }, 1000); // Adjust delay as needed
});

const sortColumn = ref('');
const sortDirection = ref('');


let pageNumber = ref(1),
    searchTerm = ref(usePage().props.search ?? ""),
    class_id = ref(usePage().props.class_id ?? ""),
    per_page = ref(usePage().props.perPage ?? ""),
    column = ref(usePage().props.sort?.column ?? ""),
    direction = ref(usePage().props.sort?.direction ?? "")

const pageNumberUpdated = (link) => {
    pageNumber.value = link.url.split("=")[1];
};

let studentsUrl = computed(() => {
    const url = new URL(route("students.index"));

    url.searchParams.set("page", pageNumber.value);

    if (searchTerm.value) {
        url.searchParams.set("search", searchTerm.value);
    }

    if (class_id.value) {
        url.searchParams.append("class_id", class_id.value);
    }

    if (per_page.value) {
        url.searchParams.append("perPage", per_page.value);
    }

    if (column.value) {
        url.searchParams.append("column", column.value);
    }

    if (direction.value) {
        url.searchParams.append("direction", direction.value);
    }

    if (sortColumn.value) {
        url.searchParams.set("sort", sortColumn.value);
        url.searchParams.set("direction", sortDirection.value);
    }

    return url;
});

watch(
    () => studentsUrl.value,
    (newValue) => {
        router.visit(newValue, {
            replace: true,
            preserveState: true,
            preserveScroll: true,
        });
    }
);

watch(
    () => searchTerm.value,
    (value) => {
        if (value) {
            pageNumber.value = 1;
        }
    }
);

const toastRef = ref(null);

onMounted(() => {
    if (toastRef.value) {
        toastRef.value.show(); // Call the show method
    }
});

const deleteForm = useForm({});

const deleteStudent = (id) => {
    deleteForm.delete(route("students.destroy", id), {
        preserveScroll: true,
        onSuccess: () => {
            showConfirmDialog.value = false;
            if (toastRef.value) {
                toastRef.value.show();
            }
        }
    });
};
const toggleStatus = async (id) => {
    try {
        const response = await axios.post(`/students/${id}/toggle-status`);
        if (response.data.success) {
            toastRef.value = response.data.message;
            router.visit(response.data.redirect);
            console.log(toastRef.value)
            if (toastRef.value) {
                toastRef.value.show();
            }
        }
    } catch (error) {
        toastRef.value = 'An error occurred while updating the status.';
        setTimeout(() => {
            toastRef.value = '';
        }, 3000);
    }
};

const showConfirmDialog = ref(false);
const showConfirmDialogStatus = ref(false);
const selectedStudentId = ref(null);

const confirmDelete = (id) => {
    selectedStudentId.value = id;
    showConfirmDialog.value = true;
};

const confirmStatus = (id) => {
    selectedStudentId.value = id;
    showConfirmDialogStatus.value = true;
};


const sort = (column) => {
    if (sortColumn.value === column) {
        // Toggle direction if same column is clicked
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
        sortColumn.value = column
    } else {
        // New column, start with ascending
        sortColumn.value = column;
        sortDirection.value = 'asc';
    }
};

const getPosts = (page = 1) => {
    console.log(sortColumn.value)
    pageNumber.value = page;
};

</script>

<template>
    <Head title="Students"/>
    <Toast v-if="message" ref="toastRef" :message="message"/>
    <ConfirmDilogue
        v-if="showConfirmDialog"
        :message="'Are you sure you want to delete student ?'"
        @close="showConfirmDialog = false"
        @confirm="deleteStudent(selectedStudentId)"
    />
    <ConfirmDilogue
        :message="'Are you sure you want to update student status?'"
        v-if="showConfirmDialogStatus"
        @close="showConfirmDialogStatus = false"
        @confirm="toggleStatus(selectedStudentId)"
    />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Students
            </h2>
        </template>
        <div class="bg-gray-100 py-10">
            <div class="mx-auto max-w-7xl">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="sm:flex sm:items-center">
                        <div class="sm:flex-auto">
                            <h1 class="text-xl font-semibold text-gray-900">
                                Students
                            </h1>
                            <p class="mt-2 text-sm text-gray-700">
                                A list of all the Students.
                            </p>
                        </div>

                        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                            <Link
                                :href="route('students.create')"
                                class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto"
                            >
                                Add Student
                            </Link>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mt-6">
                        <!-- Search Input -->
                        <div class="relative flex-1 text-sm text-gray-800">
                            <div
                                class="absolute pl-2 left-0 top-0 bottom-0 flex items-center pointer-events-none text-gray-500"
                            >
                                <MagnifyingGlass />
                            </div>
                            <input
                                id="search"
                                v-model="searchTerm"
                                class="block w-full rounded-lg border-0 py-2 pl-10 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                placeholder="Search students data..."
                                type="text"
                            />
                        </div>

                        <!-- Filter by Class -->
                        <div class="flex-1 sm:flex-none">
                            <select
                                v-model="class_id"
                                class="block w-full sm:w-auto rounded-lg border-0 py-2 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                            >
                                <option value="">Filter By Class</option>
                                <option
                                    v-for="item in classes.data"
                                    :key="item.id"
                                    :value="item.id"
                                >
                                    {{ item.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Filter by Page -->
                        <div class="flex-1 sm:flex-none">
                            <select
                                v-model="per_page"
                                class="block w-full sm:w-auto rounded-lg border-0 py-2 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                            >
                                <option value="5">Filter By Page</option>
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>


                    <div class="mt-8 flex flex-col">
                        <div
                            class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8"
                        >
                            <div
                                class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8"
                            >
                                <div
                                    class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg relative"
                                >
                                    <table
                                        class="min-w-full divide-y divide-gray-300"
                                    >
                                        <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6"
                                                scope="col"
                                            >
                                                ID
                                            </th>
                                            <th
                                                @click="sort('name')"
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6"
                                                scope="col"
                                            >
                                                Name
                                                <span v-if="sortColumn === 'name'">
                                                  {{ sortDirection === 'asc' ? '▲' : '▼' }}
                                                </span>
                                            </th>
                                            <th
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6"
                                                scope="col"
                                            >
                                                Email
                                            </th>
                                            <th
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                                scope="col"
                                            >
                                                Class
                                            </th>
                                            <th
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                                scope="col"
                                            >
                                                Section
                                            </th>
                                            <th
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                                scope="col"
                                            >
                                                Status
                                            </th>
                                            <th
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                                scope="col"
                                            >
                                                Created At
                                            </th>
                                            <th
                                                class="relative py-3.5 pl-3 pr-4 sm:pr-6"
                                                scope="col"
                                            />
                                        </tr>
                                        </thead>
                                        <tbody
                                            class="divide-y divide-gray-200 bg-white"
                                        >
                                        <!-- Skeleton Loader -->
                                        <tr v-for="n in 5" v-if="isLoading" :key="n">
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-500 sm:pl-6">
                                                <div class="h-4 bg-gray-200 rounded-md animate-pulse"></div>
                                            </td>
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-500 sm:pl-6">
                                                <div class="h-4 bg-gray-200 rounded-md animate-pulse"></div>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <div class="h-4 bg-gray-200 rounded-md animate-pulse"></div>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <div class="h-4 bg-gray-200 rounded-md animate-pulse"></div>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <div class="h-4 bg-gray-200 rounded-md animate-pulse"></div>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <div class="h-6 w-16 bg-gray-200 rounded-md animate-pulse"></div>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <div class="h-4 bg-gray-200 rounded-md animate-pulse"></div>
                                            </td>
                                            <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                <div class="h-4 bg-gray-200 rounded-md animate-pulse"></div>
                                            </td>
                                        </tr>
                                        <tr v-for="student in students.data"
                                            v-else
                                            :key="student.id"
                                        >
                                            <td
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6"
                                            >
                                                {{ student.id }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6"
                                            >
                                                {{ student.name }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"
                                            >
                                                {{ student.email }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"
                                            >
                                                {{ student.class.name }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"
                                            >
                                                {{ student.section.name }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"
                                            >
                                                <button
                                                    id="statusChangeButton"
                                                    :class="{
                                                        'bg-blue-600 text-white hover:bg-blue-700': student.status,
                                                        'bg-gray-600 text-white hover:bg-gray-700': !student.status
                                                    }"
                                                    class="px-4 py-1.5 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                                    @click="confirmStatus(student.id)"
                                                >
                                                    {{ student.status ? 'Active' : 'Inactive' }}
                                                </button>
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"
                                            >
                                                {{
                                                    student.created_at_formatted
                                                }}
                                            </td>

                                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                <!-- Edit Button with Icon -->
                                                <Link
                                                    :href="route('students.edit', student.id)"
                                                    class="text-indigo-600 hover:text-indigo-900 inline-flex items-center"
                                                >
                                                    <svg class="w-5 h-5 mr-2" fill="none"
                                                         stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M16 3l5 5-10 10H6v-5L16 3z" stroke-linecap="round"
                                                              stroke-linejoin="round"/>
                                                    </svg>
                                                    Edit
                                                </Link>
                                                &nbsp
                                                <!-- Delete Button with Icon -->
                                                <button
                                                    id="deleteButton"
                                                    class="ml-2 text-red-600 hover:text-red-800 inline-flex items-center"
                                                    @click="confirmDelete(student.id)"
                                                >
                                                    <svg class="w-5 h-5 mr-2" fill="none"
                                                         stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round"
                                                              stroke-linejoin="round"/>
                                                    </svg>
                                                    Delete
                                                </button>
                                            </td>

                                        </tr>
                                        <tr v-if="!isLoading && students.data.length === 0">
                                            <td colspan="8" class="text-center py-4 text-gray-500">
                                                No students found
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="max-w-7xl mx-auto py-6 px-0">
                                    <div class="max-w-none mx-auto">
                                        <div class="bg-white overflow-hidden shadow sm:rounded-lg">
                                            <div
                                                class="bg-white px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between border-t border-gray-200 gap-4"
                                            >
                                                <!-- Pagination Summary -->
                                                <div class="text-center sm:text-left">
                                                    <p class="text-sm text-gray-700">
                                                        Showing
                                                        <span class="font-medium">{{ students.meta.from }}</span>
                                                        to
                                                        <span class="font-medium">{{ students.meta.to }}</span>
                                                        of
                                                        <span class="font-medium">{{ students.meta.total }}</span>
                                                        results
                                                    </p>
                                                </div>

                                                <!-- Pagination Component -->
                                                <div class="flex justify-center sm:justify-end w-full sm:w-auto">
                                                    <TailwindPagination
                                                        :data="students"
                                                        :limit="1"
                                                        @pagination-change-page="getPosts"
                                                        class="text-sm"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
