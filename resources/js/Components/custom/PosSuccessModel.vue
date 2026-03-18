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
                        <DialogTitle class="text-5xl font-bold">Payment Successful!</DialogTitle>

                        <div class="w-full h-full flex flex-col justify-center items-center space-y-8 mt-4">
                            <p class="text-justify text-3xl text-black">
                                Order Payment is Successful!
                            </p>
                            <div>
                                <img src="/images/checked.png" class="h-24 object-cover w-full" />
                            </div>
                        </div>
                        <div class="flex justify-center items-center space-x-4 pt-4 mt-4">
                            <p
                                class="cursor-pointer bg-blue-600 text-white font-bold uppercase tracking-wider px-4 shadow-xl rounded py-4 rounded-xl">
                                Send Reciept To Email
                            </p>
                            <p @click="handlePrintReceipt"
                                class="cursor-pointer bg-blue-600 text-white font-bold uppercase tracking-wider px-4 shadow-xl rounded py-4 rounded-xl">
                                Print Receipt
                            </p>
                            <p @click="$emit('update:open', false)"
                                class="cursor-pointer bg-red-600 text-white font-bold uppercase tracking-wider px-4 shadow-xl rounded py-4 rounded-xl">
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
import { ref } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";

const page = usePage();

// Access the companyInfo from the page props
const companyInfo = computed(() => page.props.companyInfo);

if (companyInfo.value) {
    console.log(companyInfo.value);
} else {
    console.log('companyInfo is undefined or null');
}

const handleClose = () => {
    console.log("Modal close prevented");
};

const emit = defineEmits(["update:open"]);

// The `open` prop controls the visibility of the modal
const props = defineProps({
    open: {
        type: Boolean,
        required: true,
    },
    products: {
        type: Array,
        required: true,
    },
    cashier: Object,
    customer: Object,
    orderid: String,
    balance: Number,
    cash: Number,
    subTotal: String,
    totalDiscount: String,
    total: String,
    custom_discount: Number,
    custom_discount_type: String,
    payment_method: { type: String, default: 'cash' }
});

const handlePrintReceipt = () => {
    const companyContacts = [
        companyInfo?.value?.phone,
        companyInfo?.value?.phone2,
        companyInfo?.value?.email,
    ]
        .filter(Boolean)
        .join(" | ");

    const now = new Date();
    const billDate = now.toLocaleDateString();
    const billTime = now.toLocaleTimeString();

    const productRows = props.products
        .map((product) => {
            const isPack = Number(product.is_promotion) === 1;

            const parentRow = `
                <tr>
                    <td style="text-align:left; font-weight:600;">${product.name}</td>
                    <td style="text-align:center;">${Number(product.quantity || 0)}</td>
                    <td style="text-align:right;">
                        ${
                            product.discount > 0 && product.apply_discount
                                ? `<div style="font-weight:700;font-size:9px;border:1px solid #000;display:inline-block;padding:1px 4px;margin-bottom:2px;">${product.discount}% OFF</div>`
                                : ""
                        }
                        <div>${Number(product.selling_price || 0).toFixed(2)}</div>
                    </td>
                </tr>
            `;

            const items = Array.isArray(product.promotion_items)
                ? product.promotion_items
                : Array.isArray(product.promotionItems)
                    ? product.promotionItems
                    : [];

            let childRows = "";
            if (isPack && items.length) {
                const headingRow = `
                    <tr>
                        <td colspan="3" style="text-align:left; font-weight:700; font-size:11px;">Pack Includes</td>
                    </tr>
                `;

                const children = items
                    .map((pi) => {
                        const compName =
                            pi.product && pi.product.name
                                ? pi.product.name
                                : `#${pi.product_id}`;
                        const compQty =
                            (Number(pi.quantity) || 1) *
                            (Number(product.quantity) || 1);

                        return `
                            <tr>
                                <td style="padding-left:16px; text-align:left; font-size:11px; font-style:italic;">* ${compName}</td>
                                <td style="text-align:center; font-size:11px;">${compQty}</td>
                                <td style="text-align:right; font-size:11px;">-</td>
                            </tr>
                        `;
                    })
                    .join("");

                childRows = headingRow + children;
            }

            return parentRow + childRows;
        })
        .join("");

    const receiptHTML = `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sri Entertainment</title>
    <style>
        @media print {
            @page {
                size: 80mm auto;
                margin: 4mm;
            }
            body {
                margin: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        * {
            box-sizing: border-box;
            color: #000;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #fff;
            font-size: 11px;
        }
        .receipt-container {
            width: 72mm;
            margin: 0 auto;
            padding: 6px;
            background: #fff;
        }
        .logo-wrap {
            text-align: center;
            margin-bottom: 4px;
        }
        .logo-wrap img {
            width: 46mm;
            max-height: 22mm;
            object-fit: contain;
        }
        .company {
            text-align: center;
            font-size: 10px;
            line-height: 1.2;
        }
        .company h1 {
            margin: 2px 0;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 0.4px;
        }
        .dash {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        .title {
            text-align: center;
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 0.6px;
            margin: 2px 0;
        }
        .meta-center {
            text-align: center;
            font-size: 10px;
            line-height: 1.2;
        }
        .meta-line {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            font-size: 10px;
            margin: 2px 0;
        }
        .items {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .items th,
        .items td {
            padding: 2px 0;
            font-size: 10px;
        }
        .items th:first-child,
        .items td:first-child {
            width: 56%;
            text-align: left;
            word-break: break-word;
        }
        .items th:nth-child(2),
        .items td:nth-child(2) {
            width: 14%;
            text-align: center;
        }
        .items th:last-child,
        .items td:last-child {
            width: 30%;
            text-align: right;
        }
        .totals {
            margin-top: 2px;
            font-size: 11px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
            font-weight: 600;
        }
        .grand {
            font-size: 18px;
            font-weight: 800;
        }
        .footer {
            text-align: center;
            font-size: 9px;
            margin-top: 6px;
            line-height: 1.3;
        }
        .footer .notice {
            font-size: 10px;
            font-style: italic;
            font-weight: 700;
            margin-bottom: 3px;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="logo-wrap">
            <img src="/images/billlogo.jpeg" alt="Company Logo" />
        </div>

        <div class="company">
            <h1>Sri Entertainment</h1>
            <div>80b Hosptel Road, Kalubowala, Dehiwala</div>
            <div>0777244467 | 0766877444</div>
            <div>amirth055@gmail.com</div>
        </div>

        <div class="dash"></div>
        <div class="meta-center">${props.orderid || "-"}</div>
        <div class="meta-center">${billDate} ${billTime}</div>
        <div class="dash"></div>

        <div class="meta-line"><span><strong>Customer</strong></span><span>${props.customer?.name || "Walk-in"}</span></div>
        <div class="meta-line"><span><strong>Cashier</strong></span><span>${props.cashier?.name || "-"}</span></div>

        <div class="dash"></div>

        <table class="items">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                ${productRows}
            </tbody>
        </table>

        <div class="dash"></div>

        <div class="totals">
            <div class="total-row"><span>Sub Total</span><span>LKR ${(Number(props.subTotal) || 0).toFixed(2)}</span></div>
            <div class="total-row"><span>Discount</span><span>LKR ${(Number(props.totalDiscount) || 0).toFixed(2)}</span></div>
            <div class="total-row"><span>Custom Discount</span><span>${(Number(props.custom_discount) || 0).toFixed(2)} ${props.custom_discount_type === "percent" ? "%" : "LKR"}</span></div>
            <div class="total-row grand"><span>TOTAL</span><span>LKR ${(Number(props.total) || 0).toFixed(2)}</span></div>
            <div class="total-row"><span>Cash</span><span>LKR ${(Number(props.cash) || 0).toFixed(2)}</span></div>
            <div class="total-row"><span>Balance</span><span>LKR ${(Number(props.balance) || 0).toFixed(2)}</span></div>
        </div>

        <div class="dash"></div>
        <div class="meta-line"><span><strong>Payment Method:</strong></span><span>${props.payment_method === 'card' ? 'Card' : 'Cash'}</span></div>
        <div class="dash"></div>

        <div class="footer">
            <div class="notice">මාරු කිරීම සඳහා දින 07 ඇතුලත බිල්පත සමග පැමිණෙන්න.</div>
            <div>THANK YOU COME AGAIN</div>
            <div>Powered by JAAN Network Ltd.</div>
        </div>
    </div>
</body>
</html>
`;

    const printWindow = window.open("", "_blank");
    if (!printWindow) {
        alert("Failed to open print window. Please check your browser settings.");
        return;
    }

    printWindow.document.open();
    printWindow.document.write(receiptHTML);
    printWindow.document.close();

    printWindow.onload = () => {
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    };
};
</script>
