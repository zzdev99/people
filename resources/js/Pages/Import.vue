<template>
    <div class="flex h-screen">
        <notifications position="top right" classes="my-custom-class" :width="200" />

        <div class="container m-auto px-4 sm:px-0">
            <div class="form bg-gray-200 px-8 py-12 rounded-lg">
                <ApplicationLogo class="w-12 m-auto mb-12" />

                <Form />
            </div>
        </div>
    </div>
</template>

<script>
import ApplicationLogo from "../Components/ApplicationLogo.vue";
import Form from "../Components/Form.vue";
export default {
    name: 'Import',
    components: {
        ApplicationLogo,
        Form
    },
}
</script>
