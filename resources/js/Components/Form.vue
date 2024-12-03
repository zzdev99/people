<template>
    <div>
        <div class="col-span-full">
            <label class="block text-sm/6 font-medium text-gray-900">
                Naložite datoteko z osebami:
            </label>
            <div :class="{ dragover: isDragover }"
                @drop.prevent="handleDrop"
                @dragover.prevent="handleDragover"
                @dragleave.prevent="handleDragleave"
                class="label-container mt-2 mb-4 flex justify-center rounded-lg border border-dashed border-gray-900/25">
                <label for="file-upload"
                    class="file-upload-label px-6 py-10 relative cursor-pointer rounded-md bg-white font-semibold text-indigo-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-600 focus-within:ring-offset-2 hover:text-indigo-500">
                    <div v-show="!selectedFile" class="flex justify-center text-sm/6 text-gray-600">
                        <div>
                            <span>Naložite .CSV datoteko</span>
                            <input @input="fileInputHandler"
                                   ref="fileUpload"
                                   id="file-upload"
                                   type="file"
                                   accept=".csv"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        </div>
                    </div>
                    <div v-if="selectedFile" class="flex justify-center text-xs/5 text-gray-600">
                        <b>{{ selectedFile.name }} ({{ fileSize }})</b>
                        <span @click.prevent="removeFile" class="text-red-400 pl-2 cursor-pointer">Remove file</span>
                    </div>
                </label>
            </div>

            <button type="submit" @click="submitForm"
                class="cursor-pointer inline-block mb-2 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Uvoz podatkov
            </button>

            <Notice v-if="noticeMessage" :message="noticeMessage" :type="noticeType" />
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
            selectedFile: null,
            noticeMessage: null,
            noticeType: 'success',
            isDragover: false,
        }
    },
    methods: {
        fileInputHandler(e) {
            if (e.target.files.length > 0) {
                this.selectedFile = e.target.files[0]
            }
        },
        handleDragover() {
            this.isDragover = true
        },
        handleDragleave() {
            this.isDragover = false
        },
        handleDrop(event) {
            this.isDragover = false
            const files = event.dataTransfer.files
            if (files.length > 0) {
                if (files[0].name.toLowerCase().endsWith('.csv')) {
                    this.selectedFile = files[0]
                    const dataTransfer = new DataTransfer()
                    dataTransfer.items.add(files[0])
                    this.$refs.fileUpload.files = dataTransfer.files
                } else {
                    this.noticeMessage = 'Neveljavna končnica datoteke'
                    this.noticeType = 'error'
                }
            }
        },
        removeFile(e) {
            this.$refs.fileUpload.value = ''
            this.selectedFile = null
        },
        submitForm() {
            const file = this.$refs.fileUpload.files[0];
            if (!file){
                this.noticeMessage = 'Niste naložili datoteke'
                this.noticeType = 'error'
                return
            };

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

<style scoped>
.label-container {
    &:hover,
    &.dragover {
        border-color: #4f46e5;
        border-style: solid;
    }
}

.file-upload-label {
    display: block;
    width: 100%;
}
</style>
