<template>
    <div v-show="message">
        <p class="text-sm" :class="typeClass">
            {{ message }}
        </p>
    </div>
</template>

<script>
export default {
    name: 'Notice',
    props: {
        message: {
            type: String,
        },
        type: {
            type: String,
            default: 'success'
        }
    },
    computed: {
        typeClass() {
            if(this.type == 'success') return `text-green-600`
            else if(this.type == 'alert') return `text-orange-600`
            else if(this.type == 'error' || this.type == 'danger') return `text-red-600`
        }
    }
}
</script>
