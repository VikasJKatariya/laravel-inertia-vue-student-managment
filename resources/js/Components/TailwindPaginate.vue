<template>
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
                            @pagination-change-page="handlePaginationChange"
                            class="text-sm"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
import { defineProps } from 'vue';
import {TailwindPagination} from "laravel-vue-pagination";

const props = defineProps({
    students: Object,
    getPosts: Function,
});
const handlePaginationChange = (page) => {
    // Call the getPosts method passed from the parent component
    props.getPosts(page);
};

</script>
