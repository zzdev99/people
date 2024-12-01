<template>
    <div>
        <div class="col-span-full">
            <label class="block text-sm/6 font-medium text-gray-900">
                Naložite datoteko z osebami:
            </label>
            <div class="mt-2 mb-4 flex justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10">
                <div class="text-center">
                    <div v-show="!selectedFile" class="mt-4 flex text-sm/6 text-gray-600">
                        <label for="file-upload"
                            class="relative cursor-pointer rounded-md bg-white font-semibold text-indigo-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-600 focus-within:ring-offset-2 hover:text-indigo-500">
                            <span class="px-2">Naložite .CSV datoteko</span>
                            <input @input="fileInputHandler" ref="fileUpload" id="file-upload" type="file" accept=".csv"
                                class="sr-only">
                        </label>
                        <p class="pl-1">ali jo povlecite sem</p>
                    </div>
                    <p v-if="selectedFile" class="text-xs/5 text-gray-600">
                        <b>{{ selectedFile.name }} ({{ fileSize }})</b>

                        <span @click="removeFile" class="text-red-400 pl-2 cursor-pointer">Remove file</span>
                    </p>
                </div>
            </div>

            <button type="submit" @click="submitForm" :disabled="!selectedFile"
                class="cursor-pointer inline-block mb-2 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Naloži
            </button>

            <Notice v-if="noticeMessage" :message="noticeMessage" :type="noticeType"/>
        </div>
    </div>
</template>

<script>
import Notice from './Notice.vue';

export default {
    name: 'Form',
    components: { Notice },
    data() {
        return {
            selectedFile: false,
            noticeMessage: null,
            noticeType: 'success',
        }
    },
    methods: {
        fileInputHandler(e) {
            if (e.target.files.length > 0) {
                this.selectedFile = e.target.files[0]
            }
        },
        removeFile() {
            this.$refs.fileUpload.value = ''
            this.selectedFile = false
        },
        submitForm() {
            const file = this.$refs.fileUpload.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('file', file);

            axios.post('/api/file-upload', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then(({ data }) => {
                this.noticeMessage = data.message
                this.noticeType = 'success'
            }).catch(error => {
                this.noticeMessage = error.response.data.message
                this.noticeType = 'error'
            });
        }
    },
    computed: {
        fileSize() {
            if (!this.selectedFile) return '';

            const bytes = this.selectedFile.size;
            const units = ['B', 'KB', 'MB', 'GB'];
            let size = bytes;
            let unitIndex = 0;

            while (size >= 1024 && unitIndex < units.length - 1) {
                size /= 1024;
                unitIndex++;
            }

            return `${size.toFixed(2)} ${units[unitIndex]}`;
        }
    }
}
</script>

<style scoped></style>
