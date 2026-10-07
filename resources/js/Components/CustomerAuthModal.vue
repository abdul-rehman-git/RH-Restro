<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { X } from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Button from '@/public/components/Button.vue';
import { useCustomer } from '@/composables/useCustomer';

const { isAuthModalOpen, closeAuthModal, fetchWishlist } = useCustomer();

const activeTab = ref('login'); // 'login' or 'register'

let googleSignInUrl = '';
let googleSignUpUrl = '';
try {
    googleSignInUrl = route('auth.google.redirect', { action: 'signin' });
    googleSignUpUrl = route('auth.google.redirect', { action: 'signup' });
} catch (e) {
    // Route may not be available; Google auth links won't render
}

const loginForm = useForm({
    email: '',
    password: '',
    remember: false,
});

const registerForm = useForm({
    name: '',
    email: '',
    password: '',
    phone: '',
});

const handleLogin = async () => {
    try {
        await window.axios.post('/api/customer/login', loginForm.data());
        loginForm.reset();
        closeAuthModal();
        router.reload({ only: ['customer', 'cartCount'] });
        fetchWishlist();
    } catch (error) {
        if (error.response?.data?.errors) {
            loginForm.errors = error.response.data.errors;
        }
    }
};

const handleRegister = async () => {
    try {
        await window.axios.post('/api/customer/register', registerForm.data());
        registerForm.reset();
        closeAuthModal();
        router.reload({ only: ['customer', 'cartCount'] });
    } catch (error) {
        if (error.response?.data?.errors) {
            registerForm.errors = error.response.data.errors;
        }
    }
};
</script>

<template>
    <Modal :show="isAuthModalOpen" @close="closeAuthModal" maxWidth="md">
        <div class="relative p-6 sm:p-8 bg-card border border-border rounded-xl shadow-luxury">
            <button @click="closeAuthModal"
                class="absolute top-4 right-4 p-2 text-muted-foreground hover:text-foreground transition-colors rounded-full hover:bg-muted">
                <X class="w-5 h-5" />
            </button>

            <div v-if="activeTab === 'login'">
                <h2 class="text-2xl font-bold text-foreground mb-6 text-center">Sign In</h2>

                <a v-if="googleSignInUrl" :href="googleSignInUrl"
                    class="w-full flex items-center justify-center gap-3 px-6 py-3 mb-4 border border-border rounded-lg bg-card hover:bg-muted text-foreground font-medium transition-luxury">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Sign in with Google
                </a>

                <div class="relative mb-4">
                    <div class="absolute inset-0 flex items-center">
                        <span class="w-full border-t border-border" />
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-card px-2 text-muted-foreground">Or continue with</span>
                    </div>
                </div>

                <form @submit.prevent="handleLogin" class="space-y-4">
                    <div>
                        <InputLabel forId="login_email" value="Email" />
                        <TextInput id="login_email" type="email" v-model="loginForm.email"
                            class="public-field mt-1 block w-full" required autofocus />
                        <InputError :message="loginForm.errors.email?.[0]" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel forId="login_password" value="Password" />
                        <TextInput id="login_password" type="password" v-model="loginForm.password"
                            class="public-field mt-1 block w-full" required />
                        <InputError :message="loginForm.errors.password?.[0]" class="mt-2" />
                    </div>

                    <div class="pt-2 text-center text-sm text-muted-foreground">
                        Don't have an account?
                        <button type="button" @click="activeTab = 'register'"
                            class="text-amber-500 hover:underline font-medium">
                            Sign Up
                        </button>
                    </div>

                    <div class="pt-2">
                        <Button type="submit" class="w-full" size="lg" :disabled="loginForm.processing">
                            Sign In
                        </Button>
                    </div>
                </form>
            </div>

            <div v-else>
                <h2 class="text-2xl font-bold text-foreground mb-6 text-center">Create Account</h2>

                <a v-if="googleSignUpUrl" :href="googleSignUpUrl"
                    class="w-full flex items-center justify-center gap-3 px-6 py-3 mb-4 border border-border rounded-lg bg-card hover:bg-muted text-foreground font-medium transition-luxury">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Sign up with Google
                </a>

                <div class="relative mb-4">
                    <div class="absolute inset-0 flex items-center">
                        <span class="w-full border-t border-border" />
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-card px-2 text-muted-foreground">Or sign up with email</span>
                    </div>
                </div>

                <form @submit.prevent="handleRegister" class="space-y-4">
                    <div>
                        <InputLabel forId="reg_name" value="Full Name" />
                        <TextInput id="reg_name" type="text" v-model="registerForm.name"
                            class="public-field mt-1 block w-full" required autofocus />
                        <InputError :message="registerForm.errors.name?.[0]" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel forId="reg_email" value="Email" />
                        <TextInput id="reg_email" type="email" v-model="registerForm.email"
                            class="public-field mt-1 block w-full" required />
                        <InputError :message="registerForm.errors.email?.[0]" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel forId="reg_password" value="Password" />
                        <TextInput id="reg_password" type="password" v-model="registerForm.password"
                            class="public-field mt-1 block w-full" required />
                        <InputError :message="registerForm.errors.password?.[0]" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel forId="reg_phone" value="Phone (Optional)" />
                        <TextInput id="reg_phone" type="text" v-model="registerForm.phone"
                            class="public-field mt-1 block w-full" />
                        <InputError :message="registerForm.errors.phone?.[0]" class="mt-2" />
                    </div>

                    <div class="pt-2 text-center text-sm text-muted-foreground">
                        Already have an account?
                        <button type="button" @click="activeTab = 'login'"
                            class="text-amber-500 hover:underline font-medium">
                            Sign In
                        </button>
                    </div>

                    <div class="pt-2">
                        <Button type="submit" class="w-full" size="lg" :disabled="registerForm.processing">
                            Create Account
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </Modal>
</template>
