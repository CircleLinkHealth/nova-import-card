<template>
    <mdb-navbar animated animation="2" :class="{'is-login-route': isLoginRoute}">
        <mdb-navbar-brand>
            <a class="navbar-title" href="/">
                <img src="../../images/logos/LogoHorizontal_White.svg"
                     alt="CarePlan Manager"
                     style="height: 50px; margin-left: 5px; position:relative;">
            </a>
        </mdb-navbar-brand>
        <mdb-navbar-toggler>
            <mdb-navbar-nav right>
                <mdb-nav-item v-if="isGuest && !isLoginRoute" href="/login">
                    Provider Login
                </mdb-nav-item>
                <mdb-nav-item v-if="!isGuest" class="disabled">
                    {{userDisplayName}}
                </mdb-nav-item>
                <mdb-nav-item v-if="!isGuest" href="#" @click="logout">
                    <form id="logout-form" action="/logout" method="POST" style="display: none;">
                    </form>
                    Logout
                </mdb-nav-item>
            </mdb-navbar-nav>
        </mdb-navbar-toggler>
    </mdb-navbar>
</template>

<script>

    import {
        mdbCol,
        mdbContainer,
        mdbIcon,
        mdbInput,
        mdbNavbar,
        mdbNavbarBrand,
        mdbNavbarNav,
        mdbNavbarToggler,
        mdbNavItem,
        mdbRow
    } from 'mdbvue';

    export default {
        name: 'Navigation',
        props: ['userDisplayName', 'isGuest', 'isLoginRoute'],
        components: {
            mdbContainer,
            mdbRow,
            mdbCol,
            mdbNavbar,
            mdbNavItem,
            mdbNavbarNav,
            mdbNavbarToggler,
            mdbInput,
            mdbNavbarBrand,
            mdbIcon
        },
        methods: {
            logout() {
                const token = document.head.querySelector('meta[name="csrf-token"]');
                $('<input>')
                    .attr({
                        type: 'hidden',
                        name: '_token',
                        value: token.content
                    })
                    .appendTo('#logout-form');

                $('#logout-form').submit();
            }
        }

    }

</script>

<style>

    .navbar.is-login-route button.navbar-toggler {
        display: none;
    }

</style>
