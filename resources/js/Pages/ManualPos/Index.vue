<template>
  <Head title="Manual POS" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-4 bg-gray-100 md:px-36 px-16">
    <Header />
    <div class="w-full md:w-5/6 py-12 space-y-16">
        <div class="flex items-center justify-between space-x-4">
        <div class="flex w-full space-x-4">
          <Link href="/">
            <img src="/images/back-arrow.png" class="w-14 h-14" />
          </Link>
          <p class="pt-3 text-4xl font-bold tracking-wide text-black uppercase">
            PoS
          </p>
        </div>
        <div class="flex items-center justify-between w-full space-x-4">
          <p class="text-3xl font-bold tracking-wide text-black">
            Order ID : #{{ orderId }}
          </p>
          <p class="text-3xl text-black cursor-pointer">
            <i @click="refreshData" class="ri-restart-line"></i>
          </p>
        </div>
      </div>
    </div>

     <div class="flex md:flex-row flex-col w-full gap-4 md:px-32">
        <div class="flex flex-col md:w-1/2 w-full">
          <div class="flex flex-col w-full">
            <div class="p-16 space-y-8 bg-black shadow-lg rounded-3xl">
              <p class="mb-4 text-5xl font-bold text-white">Customer Details</p>
              <div class="mb-3">
                <input
                  v-model="customer.name"
            <title>Sri Entertainment</title>
                  placeholder="Enter Customer Name"
                  class="w-full px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                @page {
                  size: 80mm auto;
                  margin: 4mm;
                }
                />
              </div>
                <input
                  print-color-adjust: exact;
                  v-model="customer.contactNumber"
                  type="text"
              * {
                box-sizing: border-box;
                color: #000;
              }
                  placeholder="Enter Customer Contact Number"
              </div>
              <div class="text-black">
                padding: 0;
                background: #fff;
                font-size: 11px;
                  v-model="customer.email"
                  type="email"
              .receipt-container {
                width: 72mm;
                margin: 0 auto;
                padding: 6px;
              }
                  placeholder="Enter Customer Email"
                  class="w-full px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                margin-bottom: 4px;
              </div>

                margin: 2px 0;
                font-size: 18px;
                font-weight: 800;
                letter-spacing: 0.4px;
                  v-model="employee_id"
                  id="employee_id"
                  class="w-full px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                margin: 2px 0;
                  <option value="" disabled selected>Select an Employee</option>
              .logo {
                width: 46mm;
                max-height: 22mm;
                object-fit: contain;
                margin: 0 auto 4px;
                display: block;
                  >
              .dash {
                border-top: 1px dashed #000;
                margin: 6px 0;
              }
              .bill-title {
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
                  </option>
                </select>
                gap: 8px;
                font-size: 10px;
                margin: 2px 0;
          </div>
              .meta-line strong {
                font-weight: 700;
          <div class="flex flex-col items-start justify-center w-full md:px-12">
              .items {
            <div class="w-full pt-6 space-y-2">
              <div class="flex flex-col w-full pt-4 pb-4 ">
                table-layout: fixed;
              <span>
              .items th, .items td {
                padding: 2px 0;
                font-size: 10px;
                  placeholder="Enter Product Name"
              .items th:first-child,
              .items td:first-child {
                width: 56%;
                />
                word-break: break-word;
              </span>
              .items th:nth-child(2),
              .items td:nth-child(2) {
                width: 14%;
                text-align: center;
              }
              .items th:last-child,
              .items td:last-child {
                width: 30%;
                text-align: right;
                  <input
                  v-model="product_quantity"
                margin-top: 2px;
                font-size: 11px;
                  class="w-full px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              .total-row {
                </span>
              </div>
                margin: 2px 0;
                font-weight: 600;
                <p class="text-xl text-black">Unit Price</p>
              .grand {
                font-size: 18px;
                font-weight: 800;
                  type="Number"
                  placeholder="Enter unit price"
                  class="w-full px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                font-size: 9px;
                margin-top: 6px;
                line-height: 1.3;
                </span>
              </div>
                margin: 3px 0;
            
              .footer .notice {
                font-size: 10px;
              
                font-weight: 700;
              <div class="flex items-center justify-center w-full px-16 py-4">
                <button
                    @click="addProduct"
                    class="w-full px-6 py-3 text-xl font-bold text-white bg-black rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black"
                >

            <div class="header">
              <img src="/images/billlogo.jpeg" class="logo" alt="Company Logo" />
              <h1>${props.companyInfo?.name || 'PLAY AREA'}</h1>
              <p>${props.companyInfo?.address || ''}</p>
              
              <div class="flex items-center justify-between w-full px-16 pt-4 pb-4" v-for="(product, index) in products" :key="index"  >
              
              <div class="flex flex-col justify-start w-4/6">
                <p class="text-3xl text-black">
                  {{ product.name }}
                </p>
                <div class="flex items-end justify-between w-full">
                  <div class="flex space-x-4">
                    <p
                      @click="incrementQuantity(product)"

                  <div class="dash"></div>
                  <div class="meta-center">${orderId.value}</div>
                  <div class="meta-center">${new Date().toLocaleString()}</div>
                  <div class="dash"></div>
                      class="flex items-center justify-center w-8 h-8 text-white bg-black rounded cursor-pointer"
                  <div class="meta-line"><span><strong>Customer</strong></span><span>${customer.value.name || 'Walk-in'}</span></div>
                  <div class="meta-line"><span><strong>Cashier</strong></span><span>${props.loggedInUser?.name || '-'}</span></div>

                  <div class="dash"></div>

                  <table class="items">
              </div>
            </div>
                          <th>Item</th>
                          <th>Qty</th>
                          <th>Price</th>
                <p class="text-xl">{{ subtotal }} LKR</p>
              </div>
            
              <div class="flex items-center justify-between w-full px-16 pt-4 pb-4 border-b border-black">
                <p class="text-xl text-black">Custom Discount</p>
                <span>
                  <CurrencyInput
                    v-model="custom_discount"
                              <td>${product.quantity}</td>
                              <td>LKR ${product.unitPrice}</td>
                </span>
              </div>
              <div class="flex items-center justify-between w-full px-16 pt-4 pb-4 border-b border-black">
                <p class="text-xl text-black">Cash</p>
                    </table>

                  <div class="dash"></div>
                    v-model="cash"
                    :options="{ currency: 'EUR' }"
                    <div class="total-row"><span>Sub Total</span><span>LKR ${subtotal.value}</span></div>
                    <div class="total-row"><span>Custom Discount</span><span>LKR ${custom_discount.value}</span></div>
                    <div class="total-row grand"><span>TOTAL</span><span>LKR ${total.value}</span></div>
                    <div class="total-row"><span>Cash</span><span>LKR ${cash.value}</span></div>
                    <div class="total-row"><span>Balance</span><span>LKR ${balance.value}</span></div>
                class="flex items-center justify-center w-full pt-8 space-x-8"

                  <div class="dash"></div>
                  <div class="meta-line"><span><strong>Payment Method:</strong></span><span>${selectedPaymentMethod.value === 'card' ? 'Card' : 'Cash'}</span></div>
                  <div class="dash"></div>
              >
                <p class="text-xl text-black">Payment Method :</p>
                    <p class="notice">මාරු කිරීම සඳහා දින 07 ඇතුලත බිල්පත සමග පැමිණෙන්න.</p>
                    <p>THANK YOU COME AGAIN</p>
                    <p>Powered by JAAN Network (Pvt) Ltd.</p>
                    'cursor-pointer w-[100px]  border border-black rounded-xl flex flex-col justify-center items-center text-center',
                    selectedPaymentMethod === 'cash'
                      ? 'bg-yellow-500 font-bold'
                      : 'text-black',
                  ]"
                >
                  <img src="/images/money-stack.png" alt="" class="w-24" />
                </div>
                <div
                  @click="selectedPaymentMethod = 'card'"
                  :class="[
                    'cursor-pointer w-[100px] border border-black rounded-xl flex flex-col justify-center items-center text-center',
                    selectedPaymentMethod === 'card'
                      ? 'bg-yellow-500 font-bold'
                      : 'text-black',
                  ]"
                >
                  <img src="/images/bank-card.png" alt="" class="w-24" />
                </div>
              </div>

              <div class="flex items-center justify-center w-full">
                <button
                type="button"
                @click="openPrintSlip"
                :class="[
                    'w-full bg-black py-4 text-2xl font-bold tracking-wider text-center text-white uppercase rounded-xl',
                    products.length === 0
                    ? ' cursor-not-allowed'
                    : ' cursor-pointer',
                ]"
                >
                <i class="pr-4 ri-add-circle-fill"></i> Confirm Order
                </button>

              </div>
            </div>

            
          </div>
        </div>
      </div>

    
  </div>

</template>

<script setup>
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import PosSuccessModel from "@/Components/custom/PosSuccessModel.vue";
import AlertModel from "@/Components/custom/AlertModel.vue";
import { useForm, router } from "@inertiajs/vue3";
import { ref, onMounted, computed, watch } from "vue";
import { Head } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import axios from "axios";
import CurrencyInput from "@/Components/custom/CurrencyInput.vue";
import SelectProductModel from "@/Components/custom/SelectProductModel.vue";
import ProductAutoComplete from "@/Components/custom/ProductAutoComplete.vue";

const product = ref(null);
const error = ref(null);
const products = ref([]);
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
  console.log(props.products);
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