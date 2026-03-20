<template>
  <Head title="Manual POS" />
  <Banner />

  <div class="flex flex-col items-center justify-start min-h-screen px-6 py-8 space-y-6 bg-gray-100 md:px-16">
    <Header />

    <div class="flex items-center justify-between w-full max-w-6xl">
      <div class="flex items-center space-x-4">
        <Link href="/">
          <img src="/images/back-arrow.png" class="w-12 h-12" />
        </Link>
        <p class="text-3xl font-bold tracking-wide text-black uppercase">POS</p>
      </div>
      <div class="flex items-center space-x-4">
        <p class="text-2xl font-bold text-black">Order ID: #{{ orderId }}</p>
        <i @click="refreshData" class="text-2xl text-black cursor-pointer ri-restart-line"></i>
      </div>
    </div>

    <div class="grid w-full max-w-6xl grid-cols-1 gap-6 lg:grid-cols-2">
      <div class="p-6 space-y-4 bg-black rounded-2xl">
        <p class="text-3xl font-bold text-white">Customer Details</p>

        <input
          v-model="customer.name"
          type="text"
          placeholder="Enter Customer Name"
          class="w-full px-4 py-3 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        />

        <input
          v-model="customer.contactNumber"
          type="text"
          placeholder="Enter Customer Contact Number"
          class="w-full px-4 py-3 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        />

        <input
          v-model="customer.email"
          type="email"
          placeholder="Enter Customer Email"
          class="w-full px-4 py-3 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        />

        <select
          v-model="employee_id"
          id="employee_id"
          class="w-full px-4 py-3 text-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="" disabled>Select an Employee</option>
          <option v-for="employee in props.allemployee" :key="employee.id" :value="employee.id">
            {{ employee.name }}
          </option>
        </select>
      </div>

      <div class="p-6 space-y-4 bg-white shadow-lg rounded-2xl">
        <p class="text-2xl font-bold text-black">Products</p>

        <input
          v-model="product_name"
          type="text"
          placeholder="Enter Product Name"
          class="w-full px-4 py-3 text-black placeholder-black border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        />

        <div class="grid grid-cols-2 gap-4">
          <input
            v-model="product_quantity"
            type="number"
            min="1"
            placeholder="Qty"
            class="w-full px-4 py-3 text-black placeholder-black border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <input
            v-model="product_unit_price"
            type="number"
            min="0"
            step="0.01"
            placeholder="Unit Price"
            class="w-full px-4 py-3 text-black placeholder-black border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <button
          @click="addProduct"
          type="button"
          class="w-full px-4 py-3 font-bold text-white bg-black rounded-md hover:bg-gray-800"
        >
          Add Product
        </button>

        <div class="space-y-2">
          <div
            v-for="(item, index) in products"
            :key="index"
            class="flex items-center justify-between p-3 border border-gray-200 rounded-md"
          >
            <div class="w-1/2">
              <p class="font-semibold text-black">{{ item.name }}</p>
              <p class="text-sm text-gray-700">LKR {{ item.unitPrice }}</p>
            </div>
            <div class="flex items-center space-x-2">
              <button @click="decrementQuantity(item)" type="button" class="w-8 h-8 text-white bg-black rounded">-</button>
              <span class="w-6 text-center">{{ item.quantity }}</span>
              <button @click="incrementQuantity(item)" type="button" class="w-8 h-8 text-white bg-black rounded">+</button>
              <button @click="removeProduct(index)" type="button" class="px-3 py-1 text-white bg-red-600 rounded">X</button>
            </div>
          </div>
        </div>

        <div class="pt-2 space-y-2 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <p class="font-semibold text-black">Sub Total</p>
            <p class="text-black">{{ subtotal }} LKR</p>
          </div>
          <div class="flex items-center justify-between">
            <p class="font-semibold text-black">Custom Discount</p>
            <div class="w-40">
              <CurrencyInput v-model="custom_discount" :options="{ currency: 'EUR' }" />
            </div>
          </div>
          <div class="flex items-center justify-between">
            <p class="font-semibold text-black">Cash</p>
            <div class="w-40">
              <CurrencyInput v-model="cash" :options="{ currency: 'EUR' }" />
            </div>
          </div>
          <div class="flex items-center justify-between text-xl font-bold">
            <p class="text-black">Total</p>
            <p class="text-black">{{ total }} LKR</p>
          </div>
          <div class="flex items-center justify-between">
            <p class="font-semibold text-black">Balance</p>
            <p class="text-black">{{ balance }} LKR</p>
          </div>
        </div>

        <div class="flex items-center justify-center pt-2 space-x-6">
          <div
            @click="selectedPaymentMethod = 'cash'"
            :class="[
              'cursor-pointer w-[100px] border border-black rounded-xl flex flex-col justify-center items-center text-center p-2',
              selectedPaymentMethod === 'cash' ? 'bg-yellow-500 font-bold' : 'text-black',
            ]"
          >
            <img src="/images/money-stack.png" alt="Cash" class="w-16" />
            <p>Cash</p>
          </div>

          <div
            @click="selectedPaymentMethod = 'card'"
            :class="[
              'cursor-pointer w-[100px] border border-black rounded-xl flex flex-col justify-center items-center text-center p-2',
              selectedPaymentMethod === 'card' ? 'bg-yellow-500 font-bold' : 'text-black',
            ]"
          >
            <img src="/images/bank-card.png" alt="Card" class="w-16" />
            <p>Card</p>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-3 pt-3 md:grid-cols-2">
          <button
            type="button"
            @click="openPrintSlip"
            class="w-full py-3 text-lg font-bold text-black uppercase border border-black rounded-xl"
          >
            Print Slip
          </button>
          <button
            type="button"
            @click="submitOrder"
            :disabled="products.length === 0"
            :class="[
              'w-full py-3 text-lg font-bold text-white uppercase rounded-xl',
              products.length === 0 ? 'bg-gray-500 cursor-not-allowed' : 'bg-black',
            ]"
          >
            Confirm Order
          </button>
        </div>
      </div>
    </div>

    <PosSuccessModel :isOpen="isSuccessModalOpen" @update:isOpen="handleModalOpenUpdate" />
    <AlertModel :isOpen="isAlertModalOpen" :message="message" @update:isOpen="isAlertModalOpen = $event" />

    <Footer />
  </div>
</template>

<script setup>
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import PosSuccessModel from "@/Components/custom/PosSuccessModel.vue";
import AlertModel from "@/Components/custom/AlertModel.vue";
import { useForm, router } from "@inertiajs/vue3";
import { ref, onMounted, onBeforeUnmount, computed, watch } from "vue";
import { Head } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import axios from "axios";
import CurrencyInput from "@/Components/custom/CurrencyInput.vue";
import SelectProductModel from "@/Components/custom/SelectProductModel.vue";
import ProductAutoComplete from "@/Components/custom/ProductAutoComplete.vue";

const product = ref(null);
const error = ref(null);
const products = ref([]);
const appliedCoupon = ref(null);
const isSuccessModalOpen = ref(false);
const isAlertModalOpen = ref(false);
const message = ref("");
const cash = ref(0);
const product_name = ref('');
const custom_discount = ref(0);
const product_quantity = ref(1);
const product_unit_price = ref(0);


const handleModalOpenUpdate = (newValue) => {
  isSuccessModalOpen.value = newValue;
  if (!newValue) {
    refreshData();
  }
};

const addProduct = () => {
  if (product_name.value && product_quantity.value > 0 && product_unit_price.value > 0) {
    products.value.push({
      name: product_name.value,
      quantity: parseFloat(product_quantity.value),
      unitPrice: parseFloat(product_unit_price.value),
      total: parseFloat(product_quantity.value) * parseFloat(product_unit_price.value)
    });

    // Reset input fields after adding the product
    product_name.value = '';
    product_quantity.value = 1;
    product_unit_price.value = 0;
  } else {
    alert("Please enter valid product details.");
  }
};
const incrementQuantity = (product) => {
  if (product) {
    product.quantity += 1;
  }
};

const decrementQuantity = (product) => {
  if (product && product.quantity > 1) {
    product.quantity -= 1;
  }
};



const props = defineProps({
  loggedInUser: Object, // Using backend product name to avoid messing with selected products
  allcategories: Array,
  allemployee: Array,
  colors: Array,
  sizes: Array,
  companyInfo: Object,
});


const customer = ref({
  name: "",
  countryCode: "",
  contactNumber: "",
  email: "",
});

const employee_id = ref("");

const selectedPaymentMethod = ref("cash");

const refreshData = () => {
  router.visit(route("pos.index"), {
    preserveScroll: false, // Reset scroll
    preserveState: false, // Reset component state
  });
};




const orderId = computed(() => {
  const characters =
    "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
  return Array.from({ length: 6 }, () =>
    characters.charAt(Math.floor(Math.random() * characters.length))
  ).join("");
});

const submitOrder = async () => {
  // if (window.confirm("Are you sure you want to confirm the order?")) {
  console.log(products.value);
  if (balance.value < 0) {
    isAlertModalOpen.value = true;
    message.value = "Cash is not enough";
    return;
  }
  try {
    const response = await axios.post("/pos/submit", {
      customer: customer.value,
      products: products.value,
      employee_id: employee_id.value,
      paymentMethod: selectedPaymentMethod.value,
      userId: props.loggedInUser.id,
      orderId: orderId.value,
      cash: cash.value,
      custom_discount: custom_discount.value,
    });
    isSuccessModalOpen.value = true;
    console.log(response.data); // Handle success
  } catch (error) {
    if (error.response.status === 423) {
      isAlertModalOpen.value = true;
      message.value = error.response.data.message;
    }
    console.error(
      "Error submitting customer details:",
      error.response?.data || error.message
    );
    
  }
};


const subtotal = computed(() => {
  return products.value.reduce((sum, product) => {
    return sum + (product.quantity * product.unitPrice);
  }, 0).toFixed(2);
});


const totalDiscount = computed(() => {
  const productDiscount = products.value.reduce((total, item) => {
    // Check if item has a discount
    if (item.discount && item.discount > 0 && item.apply_discount == true) {
      const discountAmount =
        (parseFloat(item.selling_price) - parseFloat(item.discounted_price)) *
        item.quantity;
      return total + discountAmount;
    }
    return total; // If no discount, return total as-is
  }, 0); // Ensures two decimal places

 
  return (productDiscount).toFixed(2);
});

const total = computed(() => {
  const subtotalValue = parseFloat(subtotal.value);
  const discountValue = parseFloat(custom_discount.value) || 0;
  return (subtotalValue - discountValue).toFixed(2);
});

const balance = computed(() => {
  const cashValue = parseFloat(cash.value) || 0;
  const totalValue = parseFloat(total.value);
  return (cashValue - totalValue).toFixed(2);
});


// Check for product or handle errors
const form = useForm({
  employee_id: "",
  barcode: "", // Form field for barcode
});

const couponForm = useForm({
  code: "",
});

// Temporary barcode storage during scanning
let barcode = "";
let timeout; // Timeout to detect the end of the scan

const handleScannerInput = (event) => {
  if (event.key === "Enter") {
    form.barcode = barcode;
    barcode = "";
    return;
  }

  if (event.key.length === 1) {
    barcode += event.key;
  }

  clearTimeout(timeout);
  timeout = setTimeout(() => {
    barcode = "";
  }, 100);
};

const submitCoupon = async () => {
  try {
    const response = await axios.post(route("pos.getCoupon"), {
      code: couponForm.code, // Send the coupon field
    });

    const { coupon: fetchedCoupon, error: fetchedError } = response.data;

    if (fetchedCoupon) {
      appliedCoupon.value = fetchedCoupon;
      products.value.forEach((product) => {
        product.apply_discount = false;
      });
    } else {
      isAlertModalOpen.value = true;
      message.value = fetchedError;
      error.value = fetchedError;
    }
  } catch (err) {
    // console.error(error);
    if (err.response.status === 422) {
      isAlertModalOpen.value = true;
      message.value = err.response.data.message;
    }
  }
};

const removeProduct = (index) => {
  products.value.splice(index, 1);
};

watch([cash, custom_discount], ([newCash, newDiscount]) => {
  cash.value = parseFloat(newCash) || 0;
  custom_discount.value = parseFloat(newDiscount) || 0;
});

// Attach the keypress event listener when the component is mounted
onMounted(() => {
  document.addEventListener("keypress", handleScannerInput);
});

onBeforeUnmount(() => {
  document.removeEventListener("keypress", handleScannerInput);
});

const openPrintSlip = () => {
  const printContent = `
    <!DOCTYPE html>
    <html lang="en">
      <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                font-family: 'Arial', sans-serif;
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
      .meta-row {
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
      .notice {
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
      <div class="meta-center">${orderId.value || '-'}</div>
      <div class="meta-center">${new Date().toLocaleString()}</div>
      <div class="dash"></div>

      <div class="meta-row"><span><strong>Customer</strong></span><span>${customer.value?.name || 'Walk-in'}</span></div>
      <div class="meta-row"><span><strong>Cashier</strong></span><span>${props.loggedInUser?.name || '-'}</span></div>

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
        ${products.value
        .map(
          (product) => `
          <tr>
            <td>${product.name}</td>
            <td style="text-align:center;">${product.quantity}</td>
            <td style="text-align:right;">LKR ${Number(product.unitPrice || 0).toFixed(2)}</td>
          </tr>`
        )
        .join("")}
      </tbody>
      </table>

      <div class="dash"></div>

      <div class="totals">
      <div class="total-row"><span>Sub Total</span><span>LKR ${Number(subtotal.value || 0).toFixed(2)}</span></div>
      <div class="total-row"><span>Custom Discount</span><span>LKR ${Number(custom_discount.value || 0).toFixed(2)}</span></div>
      <div class="total-row grand"><span>TOTAL</span><span>LKR ${Number(total.value || 0).toFixed(2)}</span></div>
      <div class="total-row"><span>Cash</span><span>LKR ${Number(cash.value || 0).toFixed(2)}</span></div>
      <div class="total-row"><span>Balance</span><span>LKR ${Number(balance.value || 0).toFixed(2)}</span></div>
      </div>

      <div class="dash"></div>
      <div class="meta-row"><span><strong>Payment Method:</strong></span><span>${selectedPaymentMethod.value === 'card' ? 'Card' : 'Cash'}</span></div>
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

  // Write the content to the new window
  printWindow.document.open();
  printWindow.document.write(printContent); // Changed from openPrintSlip to printContent
  printWindow.document.close();

  // Wait for the content to load before triggering print
  printWindow.onload = () => {
    printWindow.focus();
    printWindow.print();
    printWindow.close();
  };
};


</script>