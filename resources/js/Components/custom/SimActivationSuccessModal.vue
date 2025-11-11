<template>
    <TransitionRoot as="template" :show="open" static>
        <Dialog class="relative z-10" static>
            <!-- Modal Overlay -->
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click.stop />
            </TransitionChild>

            <!-- Modal Content -->
            <div class="fixed inset-0 z-10 flex items-center justify-center">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 scale-95"
                    enter-to="opacity-100 scale-100" leave="ease-in duration-200" leave-from="opacity-100 scale-100"
                    leave-to="opacity-0 scale-95">
                    <DialogPanel
                        class="bg-white border-4 border-blue-600 rounded-[20px] shadow-xl max-w-xl w-full p-6 text-center">
                        <!-- Modal Title -->
                        <DialogTitle class="text-5xl font-bold">Transaction Successful!</DialogTitle>

                        <div class="w-full h-full flex flex-col justify-center items-center space-y-8 mt-4">
                            <p class="text-3xl text-black text-center">
                                {{ transaction.sim_stock_id ? 'SIM Activation' : 'Reload' }} completed successfully!
                            </p>
                            <div>
                                <img src="/images/checked.png" class="h-24 object-cover w-full" />
                            </div>
                        </div>
                        <div class="flex justify-center items-center space-x-4 pt-4 mt-4">
                            <p @click="handlePrintReceipt"
                                class="cursor-pointer bg-blue-600 text-white font-bold uppercase tracking-wider px-4 shadow-xl py-4 rounded-xl">
                                Print Receipt
                            </p>
                            <p @click="$emit('update:open', false)"
                                class="cursor-pointer bg-red-600 text-white font-bold uppercase tracking-wider px-4 shadow-xl py-4 rounded-xl">
                                Close
                            </p>
                        </div>
                    </DialogPanel>
                </TransitionChild>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from "@headlessui/vue";
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();

// Access the companyInfo from the page props
const companyInfo = computed(() => page.props.companyInfo);

const emit = defineEmits(["update:open"]);

// Props
const props = defineProps({
    open: {
        type: Boolean,
        required: true,
    },
    transaction: {
        type: Object,
        required: true,
    },
    cashier: Object,
});

const handlePrintReceipt = () => {
    // Helper functions
    const f2 = (v) => (Number(v ?? 0)).toFixed(2);

    // Generate the receipt HTML
    const receiptHTML = `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - ${props.transaction.transaction_number}</title>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        body {
            background-color: #ffffff;
            font-size: 12px;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 10px;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 16px;
        }
        .header h1 {
            font-size: 20px;
            font-weight: bold;
            margin: 0;
        }
        .header p {
            font-size: 10px;
            margin: 4px 0;
        }
        .section {
            margin-bottom: 12px;
            padding-top: 8px;
            border-top: 1px solid #000;
        }
        .info-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            font-size: 11px;
            margin-top: 4px;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-row p {
            margin: 0;
            font-weight: bold;
            font-size: 10px;
        }
        .info-row small {
            font-weight: normal;
            font-size: 11px;
            margin-top: 2px;
        }
        table {
            width: 100%;
            font-size: 12px;
            border-collapse: collapse;
            margin-top: 8px;
            table-layout: fixed;
        }
        table th, table td {
            padding: 6px 8px;
            word-wrap: break-word;
        }
        table th {
            text-align: left;
        }
        table td {
            text-align: right;
        }
        table td:first-child {
            text-align: left;
        }
        .totals {
            border-top: 2px solid #000;
            padding-top: 12px;
            margin-top: 12px;
            font-size: 14px;
        }
        .totals div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 16px;
        }
        .footer p {
            margin: 6px 0;
        }
        .footer .italic {
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <img src="/images/billlogo.png" style="width:300px;height:120px;" />
            ${companyInfo?.value?.name ? `<h1>${companyInfo.value.name}</h1>` : ''}
            ${companyInfo?.value?.address ? `<p>${companyInfo.value.address}</p>` : ''}
            ${
                (companyInfo?.value?.phone || companyInfo?.value?.phone2 || companyInfo?.value?.email)
                    ? `<p>${companyInfo.value.phone || ''} | ${companyInfo.value.phone2 || ''} ${companyInfo.value.email || ''}</p>`
                    : ''
            }
        </div>

        <div class="section">
            <div class="info-row">
                <div class="info-item">
                    <p>Date:</p>
                    <small>${new Date(props.transaction.transaction_date).toLocaleDateString()}</small>
                </div>
                <div class="info-item">
                    <p>Receipt No:</p>
                    <small>${props.transaction.transaction_number || 'N/A'}</small>
                </div>
            </div>
            <div class="info-row">
                <div class="info-item">
                    <p>Operator:</p>
                    <small>${props.transaction.operator_name || 'N/A'}</small>
                </div>
                <div class="info-item">
                    <p>Cashier:</p>
                    <small>${props.cashier?.name || props.transaction.user?.name || 'Staff'}</small>
                </div>
            </div>
            ${props.transaction.mobile_number ? `
            <div class="info-row">
                <div class="info-item">
                    <p>Mobile:</p>
                    <small>${props.transaction.mobile_number}</small>
                </div>
                <div class="info-item">
                    <p>Type:</p>
                    <small>${props.transaction.sim_stock_id ? 'SIM Activation' : 'Reload'}</small>
                </div>
            </div>
            ` : ''}
        </div>

        <div class="section">
            <table>
                <colgroup>
                    <col style="width:60%;">
                    <col style="width:15%;">
                    <col style="width:25%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>Items</th>
                        <th style="text-align:center;">Qty</th>
                        <th style="text-align:right;">Price</th>
                    </tr>
                </thead>
                <tbody style="font-size:11px;">
                    ${props.transaction.sim_stock_id ? `
                    <tr>
                        <td>SIM Card - ${props.transaction.operator_name}</td>
                        <td style="text-align:center;">1</td>
                        <td style="text-align:right;">${f2(props.transaction.sim_revenue)} LKR</td>
                    </tr>
                    ` : ''}
                    <tr>
                        <td>${props.transaction.sim_stock_id ? 'Reload Package' : 'Reload'} - ${f2(props.transaction.package_face_value)} LKR</td>
                        <td style="text-align:center;">1</td>
                        <td style="text-align:right;">${f2(props.transaction.package_revenue)} LKR</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="totals">
            <div>
                <span>Total</span>
                <span>${f2(props.transaction.total_revenue)} LKR</span>
            </div>
        </div>

        ${props.transaction.notes ? `
        <div class="section">
            <div style="margin-top:8px;">
                <strong>Notes:</strong> ${props.transaction.notes}
            </div>
        </div>
        ` : ''}

        <div class="footer">
            <p style="font-weight:bold; font-style:italic; padding:4px 0; font-size:14px; color:#000;">
                මාරු කිරීම සඳහා දින 07 ඇතුලත බිල්පත සමග පැමිණෙන්න.
            </p>
            <p>THANK YOU COME AGAIN</p>
            <p class="italic">Let the quality define its own standards</p>
            <p style="font-weight:bold;">Powered by JAAN Network Ltd.</p>
            <p>${new Date().toLocaleTimeString()}</p>
        </div>
    </div>
</body>
</html>
    `;

    // Open a new window
    const printWindow = window.open("", "_blank");
    if (!printWindow) {
        alert("Failed to open print window. Please check your browser settings.");
        return;
    }

    // Write the content to the new window
    printWindow.document.open();
    printWindow.document.write(receiptHTML);
    printWindow.document.close();

    // Wait for the content to load before triggering print
    printWindow.onload = () => {
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    };
};
</script>
