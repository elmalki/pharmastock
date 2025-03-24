<template>
    <main>
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl items-start gap-x-8 gap-y-8 lg:mx-0 lg:max-w-none ">
                <div class="-mx-4 px-4 py-8 shadow-sm ring-1 ring-gray-900/5 sm:mx-0 sm:rounded-lg sm:px-8 sm:pb-14 lg:col-span-2 lg:row-span-2 lg:row-end-2 xl:px-16 xl:pb-20 xl:pt-16">
                    <h2 class="text-base font-semibold text-gray-900">Commande</h2>
                    <dl class="mt-6 grid grid-cols-1 text-sm/6 sm:grid-cols-2">
                        <div class="sm:pr-4">
                            <dt class="inline text-gray-500">Date </dt>
                            {{ ' ' }}
                            <dd class="inline text-gray-700"><time datetime="2023-23-01">January 23, 2023</time></dd>
                        </div>
                        <div class="mt-2 sm:mt-0 sm:pl-4">
                            <dt class="inline text-gray-500">Date Échéance</dt>
                            {{ ' ' }}
                            <dd class="inline text-gray-700"><time datetime="2023-31-01">January 31, 2023</time></dd>
                        </div>
                        <div class="mt-6 border-t border-gray-900/5 pt-6 sm:pr-4">
                            <dt class="font-semibold text-gray-900">From</dt>
                            <dd class="mt-2 text-gray-500"><span class="font-medium text-gray-900">Acme, Inc.</span><br />7363 Cynthia Pass<br />Toronto, ON N3Y 4H8</dd>
                        </div>
                        <div class="mt-6 sm:mt-6 sm:border-t sm:border-gray-900/5 sm:pl-4 sm:pt-6">
                            <dt class="font-semibold text-gray-900">To</dt>
                            <dd class="mt-2 text-gray-500"><span class="font-medium text-gray-900">Tuple, Inc</span><br />886 Walter Street<br />New York, NY 12345</dd>
                        </div>
                    </dl>
                    <table class="mt-16 w-full whitespace-nowrap text-left text-sm/6">
                        <colgroup>
                            <col class="w-full" />
                            <col />
                            <col />
                            <col />
                        </colgroup>
                        <thead class="border-b border-gray-200 text-gray-900">
                        <tr>
                            <th scope="col" class="px-0 py-3 font-semibold">Projects</th>
                            <th scope="col" class="hidden py-3 pl-8 pr-0 text-right font-semibold sm:table-cell">Hours</th>
                            <th scope="col" class="hidden py-3 pl-8 pr-0 text-right font-semibold sm:table-cell">Rate</th>
                            <th scope="col" class="py-3 pl-8 pr-0 text-right font-semibold">Price</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="item in invoice.items" :key="item.id" class="border-b border-gray-100">
                            <td class="max-w-0 px-0 py-5 align-top">
                                <div class="truncate font-medium text-gray-900">{{ item.title }}</div>
                                <div class="truncate text-gray-500">{{ item.description }}</div>
                            </td>
                            <td class="hidden py-5 pl-8 pr-0 text-right align-top tabular-nums text-gray-700 sm:table-cell">{{ item.hours }}</td>
                            <td class="hidden py-5 pl-8 pr-0 text-right align-top tabular-nums text-gray-700 sm:table-cell">{{ item.rate }}</td>
                            <td class="py-5 pl-8 pr-0 text-right align-top tabular-nums text-gray-700">{{ item.price }}</td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <th scope="row" class="px-0 pb-0 pt-6 font-normal text-gray-700 sm:hidden">Subtotal</th>
                            <th scope="row" colspan="3" class="hidden px-0 pb-0 pt-6 text-right font-normal text-gray-700 sm:table-cell">Subtotal</th>
                            <td class="pb-0 pl-8 pr-0 pt-6 text-right tabular-nums text-gray-900">{{ invoice.subTotal }}</td>
                        </tr>
                        <tr>
                            <th scope="row" class="pt-4 font-normal text-gray-700 sm:hidden">Tax</th>
                            <th scope="row" colspan="3" class="hidden pt-4 text-right font-normal text-gray-700 sm:table-cell">Tax</th>
                            <td class="pb-0 pl-8 pr-0 pt-4 text-right tabular-nums text-gray-900">{{ invoice.tax }}</td>
                        </tr>
                        <tr>
                            <th scope="row" class="pt-4 font-semibold text-gray-900 sm:hidden">Total</th>
                            <th scope="row" colspan="3" class="hidden pt-4 text-right font-semibold text-gray-900 sm:table-cell">Total</th>
                            <td class="pb-0 pl-8 pr-0 pt-4 text-right font-semibold tabular-nums text-gray-900">{{ invoice.total }}</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </main>
</template>

<script setup>
import { ref } from 'vue'
import {
    Dialog,
    DialogPanel,
    Listbox,
    ListboxButton,
    ListboxLabel,
    ListboxOption,
    ListboxOptions,
    Menu,
    MenuButton,
    MenuItem,
    MenuItems,
} from '@headlessui/vue'
import {
    Bars3Icon,
    CalendarDaysIcon,
    CreditCardIcon,
    EllipsisVerticalIcon,
    FaceFrownIcon,
    FaceSmileIcon,
    FireIcon,
    HandThumbUpIcon,
    HeartIcon,
    PaperClipIcon,
    UserCircleIcon,
    XMarkIcon as XMarkIconMini,
} from '@heroicons/vue/20/solid'
import { BellIcon, XMarkIcon as XMarkIconOutline } from '@heroicons/vue/24/outline'
import { CheckCircleIcon } from '@heroicons/vue/24/solid'

const navigation = [
    { name: 'Home', href: '#' },
    { name: 'Invoices', href: '#' },
    { name: 'Clients', href: '#' },
    { name: 'Expenses', href: '#' },
]
const invoice = {
    subTotal: '$8,800.00',
    tax: '$1,760.00',
    total: '$10,560.00',
    items: [
        {
            id: 1,
            title: 'Logo redesign',
            description: 'New logo and digital asset playbook.',
            hours: '20.0',
            rate: '$100.00',
            price: '$2,000.00',
        },
        {
            id: 2,
            title: 'Website redesign',
            description: 'Design and program new company website.',
            hours: '52.0',
            rate: '$100.00',
            price: '$5,200.00',
        },
        {
            id: 3,
            title: 'Business cards',
            description: 'Design and production of 3.5" x 2.0" business cards.',
            hours: '12.0',
            rate: '$100.00',
            price: '$1,200.00',
        },
        {
            id: 4,
            title: 'T-shirt design',
            description: 'Three t-shirt design concepts.',
            hours: '4.0',
            rate: '$100.00',
            price: '$400.00',
        },
    ],
}
const activity = [
    { id: 1, type: 'created', person: { name: 'Chelsea Hagon' }, date: '7d ago', dateTime: '2023-01-23T10:32' },
    { id: 2, type: 'edited', person: { name: 'Chelsea Hagon' }, date: '6d ago', dateTime: '2023-01-23T11:03' },
    { id: 3, type: 'sent', person: { name: 'Chelsea Hagon' }, date: '6d ago', dateTime: '2023-01-23T11:24' },
    {
        id: 4,
        type: 'commented',
        person: {
            name: 'Chelsea Hagon',
            imageUrl:
                'https://images.unsplash.com/photo-1550525811-e5869dd03032?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
        },
        comment: 'Called client, they reassured me the invoice would be paid by the 25th.',
        date: '3d ago',
        dateTime: '2023-01-23T15:56',
    },
    { id: 5, type: 'viewed', person: { name: 'Alex Curren' }, date: '2d ago', dateTime: '2023-01-24T09:12' },
    { id: 6, type: 'paid', person: { name: 'Alex Curren' }, date: '1d ago', dateTime: '2023-01-24T09:20' },
]
const moods = [
    { name: 'Excited', value: 'excited', icon: FireIcon, iconColor: 'text-white', bgColor: 'bg-red-500' },
    { name: 'Loved', value: 'loved', icon: HeartIcon, iconColor: 'text-white', bgColor: 'bg-pink-400' },
    { name: 'Happy', value: 'happy', icon: FaceSmileIcon, iconColor: 'text-white', bgColor: 'bg-green-400' },
    { name: 'Sad', value: 'sad', icon: FaceFrownIcon, iconColor: 'text-white', bgColor: 'bg-yellow-400' },
    { name: 'Thumbsy', value: 'thumbsy', icon: HandThumbUpIcon, iconColor: 'text-white', bgColor: 'bg-blue-500' },
    { name: 'I feel nothing', value: null, icon: XMarkIconMini, iconColor: 'text-gray-400', bgColor: 'bg-transparent' },
]

const mobileMenuOpen = ref(false)
const selected = ref(moods[5])
</script>
