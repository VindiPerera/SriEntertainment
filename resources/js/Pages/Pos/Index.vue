<template>

    <Head title="POS" />
    <Banner />
    <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-4 bg-gray-100 md:px-36 px-16">
        <!-- Include the Header -->
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
                        Order ID : #{{ orderid  }}
                    </p>
                    <p class="text-3xl text-black cursor-pointer">
                        <i @click="refreshData" class="ri-restart-line"></i>
                    </p>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="flex space-x-2 border-b-2 border-gray-300">
                <button
                    @click="activeTab = 'products'"
                    :class="[
                        'px-8 py-4 text-xl font-bold tracking-wide transition-all duration-200',
                        activeTab === 'products'
                            ? 'bg-black text-white border-b-4 border-yellow-500'
                            : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                    ]"
                >
                    🛒 Products
                </button>
                <button
                    @click="activeTab = 'sim'"
                    :class="[
                        'px-8 py-4 text-xl font-bold tracking-wide transition-all duration-200',
                        activeTab === 'sim'
                            ? 'bg-black text-white border-b-4 border-yellow-500'
                            : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                    ]"
                >
                    📱 SIM Activation
                </button>
                <button
                    @click="activeTab = 'reload'"
                    :class="[
                        'px-8 py-4 text-xl font-bold tracking-wide transition-all duration-200',
                        activeTab === 'reload'
                            ? 'bg-black text-white border-b-4 border-yellow-500'
                            : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                    ]"
                >
                    💰 Sell Reload
                </button>
            </div>
            
            <!-- Products Tab Content -->
            <div v-show="activeTab === 'products'" class="flex md:flex-row flex-col w-full gap-4">
                <div class="flex flex-col md:w-1/2 w-full">
                    <div class="flex flex-col w-full">
                        <div class="p-16 space-y-8 bg-black shadow-lg rounded-3xl">
                            <p class="mb-4 text-5xl font-bold text-white">Customer Details</p>
                            <div class="mb-3">
                                <input v-model="customer.name" type="text" placeholder="Enter Customer Name"
                                    class="w-full px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div class="flex gap-2 mb-3 text-black">
                                <!-- <select
                  v-model="customer.countryCode"
                  class="w-[60px] px-2 py-2 bg-white placeholder-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="+94">+94</option>
                  <option value="+1">+1</option>
                  <option value="+44">+44</option>
                </select> -->
                                <input v-model="customer.contactNumber" type="text"
                                    placeholder="Enter Customer Contact Number"
                                    class="flex-grow px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div class="text-black">
                                <input v-model="customer.email" type="email" placeholder="Enter Customer Email"
                                    class="w-full px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>

                            <div class="text-black">
                                <select required v-model="employee_id" id="employee_id"
                                    class="w-full px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="" disabled selected>Select an Employee</option>
                                    <option v-for="employee in allemployee" :key="employee.id" :value="employee.id">
                                        {{ employee.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col items-center justify-center w-full md:pt-32 py-8 md:py-0 space-y-8">
                        <img src="/images/Fading wheel.gif" class="object-cover w-32 h-32 rounded-full" />
                        <p class="text-3xl text-black">
                            Bar Code Scanner is in Progress...
                        </p>
                    </div>
                </div>
                <div class="flex md:w-1/2 w-full p-8 border-4 border-black rounded-3xl">
                    <div class="flex flex-col items-start justify-center w-full md:px-12 px-4">
                        <div class="flex items-center justify-between w-full">
                            <h2 class="md:text-5xl text-4xl font-bold text-black">Billing Details</h2>
                            <div class="flex items-center">
                              <!-- Custom Dropdown Button -->
                              <div class="relative">
                                <button
                                  @click="isDropdownOpen = !isDropdownOpen"
                                  class="flex items-center gap-3 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-lg transition-all duration-200 min-w-[130px]"
                                >
                                  <span>User Manual</span>
                                  <svg 
                                    :class="['w-4 h-4 transition-transform duration-200', isDropdownOpen ? 'rotate-180' : '']"
                                    fill="none" 
                                    stroke="currentColor" 
                                    viewBox="0 0 24 24"
                                  >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                  </svg>
                                </button>
                                
                                <!-- Dropdown Menu -->
                                <div 
                                  v-show="isDropdownOpen"
                                  class="absolute top-full left-0 mt-2 w-full bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50"
                                >
                                  <button
                                    v-for="option in dropdownOptions"
                                    :key="option.value"
                                    @click="selectOption(option.value)"
                                    class="w-full px-4 py-3 text-left text-gray-700 hover:bg-gray-100 flex items-center gap-3 transition-colors duration-150"
                                  >
                                    <span>{{ option.icon }}</span>
                                    <span>{{ option.label }}</span>
                                  </button>
                                </div>
                              </div>
                            </div>
                        </div>

                        <div class="flex items-end justify-between w-full my-5 border-2 border-black rounded-2xl">
                            <div class="flex items-center justify-center w-3/4">
                                <label for="search" class="text-xl font-medium text-gray-800"></label>
                                <input v-model="form.barcode" id="search" type="text" placeholder="Enter BarCode Here!"
                                    class="w-full h-16 px-4 rounded-l-2xl focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div class="flex items-end justify-end w-1/4">
                                <button @click="submitBarcode"
                                    class="px-12 py-4 text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 rounded-r-xl">
                                    Enter
                                </button>
                            </div>
                        </div>

                        <!-- <div class="max-w-xs relative space-y-3">
              <label for="search" class="text-gray-900">
                Type the product name to search
              </label>

              <input
                v-model="form.barcode"
                id="search"
                type="text"
                placeholder="Enter BarCode Here!"
                class="w-full h-16 px-4 rounded-l-2xl focus:outline-none focus:ring-2 focus:ring-blue-500"
              />

              <ul
                v-if="searchResults.length"
                class="w-full rounded bg-white border border-gray-300 px-4 py-2 space-y-1 absolute z-10"
              >
                <li class="px-1 pt-1 pb-2 font-bold border-b border-gray-200">
                  Showing {{ searchResults.length }} results
                </li>
                <li
                  v-for="product in searchResults"
                  :key="product.id"
                  @click="selectProduct(product.name)"
                  class="cursor-pointer hover:bg-gray-100 p-1"
                >
                  {{ product.name }}
                </li>
              </ul>

              <p v-if="form.barcode" class="text-lg pt-2 absolute">
                You have selected:
                <span class="font-semibold">{{ form.barcode }}</span>
              </p>
            </div> -->

                        <div class="w-full text-center">
                            <p v-if="products.length === 0" class="text-2xl text-red-500">
                                No Products to show
                            </p>
                        </div>

                        <div class="flex items-center w-full py-4 border-b border-black" v-for="item in products"
                            :key="item.id">
                            <div class="flex w-1/6">
                                <img :src="item.image ? `/${item.image}` : '/images/placeholder.jpg'
                                    " alt="Supplier Image" class="object-cover w-16 h-16 border border-gray-500" />
                            </div>
                            <div class="flex flex-col justify-between w-5/6">
                                <p class="text-xl text-black">
                                    {{ item.name }}


                                </p>

                                <div
  v-if="Number(item.is_promotion) === 1"
  class="mt-2 rounded-lg border border-gray-200 p-3 bg-gray-50"
>
  <p class="text-md font-bold text-black mb-2">
    Pack items
  </p>

  <!-- Scrollable list -->
  <ul
    class="mt-1 list-disc pl-5 space-y-1 max-h-40 overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100"
  >
    <li
      v-for="pi in item.promotion_items ?? []"
      :key="pi.id"
      class="text-sm text-gray-700 bg-white rounded-md px-2 py-1 shadow-sm hover:bg-gray-50"
    >

      <span v-if="pi.product">  {{ pi.product.name }}</span>
      <span class="ml-2 text-lg text-dark">× {{ pi.quantity }}</span>
    </li>
  </ul>
</div>
                                <div class="flex items-center justify-between w-full">
                                    <div class="flex space-x-4">
                                        <p @click="incrementQuantity(item.id)"
                                            class="flex items-center justify-center w-8 h-8 text-white bg-black rounded cursor-pointer">
                                            <i class="ri-add-line"></i>
                                        </p>
                                        <!-- <p
                      class="bg-[#D9D9D9] border-2 border-black h-8 w-8 text-black flex justify-center items-center rounded"
                    >
                      {{ item.quantity }}
                    </p> -->
                                        <input type="number" v-model="item.quantity" min="0"
                                            class="bg-[#D9D9D9] border-2 border-black h-8 w-24 text-black flex justify-center items-center rounded text-center" />
                                        <p @click="decrementQuantity(item.id)"
                                            class="flex items-center justify-center w-8 h-8 text-white bg-black rounded cursor-pointer">
                                            <i class="ri-subtract-line"></i>
                                        </p>
                                    </div>
                                    <div class="flex items-center justify-center">
                                        <div>
                                            <p @click="applyDiscount(item.id)" v-if="
                                                item.discount &&
                                                item.discount > 0 &&
                                                item.apply_discount == false &&
                                                !appliedCoupon
                                            "
                                                class="cursor-pointer py-1 text-center px-4 bg-green-600 rounded-xl font-bold text-white tracking-wider">
                                                Apply {{ item.discount }}% off
                                            </p>

                                            <p v-if="
                                                item.discount &&
                                                item.discount > 0 &&
                                                item.apply_discount == true &&
                                                !appliedCoupon
                                            " @click="removeDiscount(item.id)"
                                                class="cursor-pointer py-1 text-center px-4 bg-red-600 rounded-xl font-bold text-white tracking-wider">
                                                Remove {{ item.discount }}% Off
                                            </p>
                                            <p class="text-2xl font-bold text-black text-right">
                                                {{ item.selling_price }}
                                                LKR
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-end w-1/6">
                                <p @click="removeProduct(item.id)"
                                    class="text-3xl text-black border-2 border-black rounded-full cursor-pointer">
                                    <i class="ri-close-line"></i>
                                </p>
                            </div>
                        </div>
                        <div class="w-full pt-6 space-y-2">
                            <div class="flex items-center justify-between w-full px-8">
                                <p class="text-xl">Sub Total</p>
                                <p class="text-xl">{{ subtotal }} LKR</p>
                            </div>
                            <div class="flex items-center justify-between w-full px-8 py-2 pb-4 border-b border-black">
                                <p class="text-xl">Discount</p>
                                <p class="text-xl">( {{ totalDiscount }} LKR )</p>
                            </div>
                            <!-- <div class="flex items-center justify-between w-full px-8 pt-4 pb-4 border-b border-black">
                <p class="text-xl text-black">Custom Discount</p>
                <span>
                  <CurrencyInput
                    v-model="custom_discount"
                  />
                  <span class="ml-2">LKR</span>
                </span>
              </div> -->




                            <div class="flex items-center justify-between w-full px-8 pt-4 pb-4 border-b border-black">
                                <p class="text-xl text-black">Custom Discount</p>
                                <span class="flex items-center">
                                    <CurrencyInput v-model="custom_discount" @blur="validateCustomDiscount"
                                        placeholder="Enter value" class=" rounded-md px-2 py-1 text-black text-md" />
                                    <select v-model="custom_discount_type"
                                        class="ml-2 px-8 border-black rounded-md text-black   py-1 text-md  ">
                                        <option value="percent">%</option>
                                        <option value="fixed">Rs</option>
                                    </select>
                                </span>
                            </div>








                            <div class="flex items-center justify-between w-full px-8 pt-4 pb-4 border-b border-black">
                                <p class="text-xl text-black">Cash</p>
                                <span>
                                    <CurrencyInput v-model="cash" :options="{ currency: 'EUR' }" />
                                    <span class="ml-2">LKR</span>
                                </span>
                            </div>
                            <div class="flex items-center justify-between w-full px-8 pt-4">
                                <p class="text-3xl text-black">Total</p>
                                <p class="text-3xl text-black">{{ total }} LKR</p>
                            </div>


                            <div class="flex items-center justify-between w-full px-8 pt-4 pb-4 border-b border-black">
                                <p class="text-xl text-black">Balance</p>
                                <p>{{ balance }} LKR</p>
                            </div>
                        </div>

                        <div class="w-full my-5">
                            <div class="relative flex items-center">
                                <!-- Input Field -->
                                <label for="coupon" class="sr-only">Coupon Code</label>
                                <input id="coupon" v-model="couponForm.code" type="text" placeholder="Enter Coupon Code"
                                    class="w-full h-16 px-6 pr-40 text-lg text-gray-800 placeholder-gray-500 border-2 border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />

                                <template v-if="!appliedCoupon">
                                    <button type="button" @click="submitCoupon"
                                        class="absolute right-2 top-2 h-12 px-6 text-lg font-semibold text-white uppercase bg-blue-600 rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        Apply Coupon
                                    </button>
                                </template>
                                <template v-else>
                                    <button type="button" @click="removeCoupon"
                                        class="absolute right-2 top-2 h-12 px-6 text-lg font-semibold text-white uppercase bg-red-600 rounded-full hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                        Remove Coupon
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div class="flex flex-col w-full space-y-8">
                            <div class="flex items-center justify-center w-full pt-8 space-x-8">
                                <p class="text-xl text-black">Payment Method :</p>
                                <div @click="selectedPaymentMethod = 'cash'" :class="[
                                    'cursor-pointer w-[100px]  border border-black rounded-xl flex flex-col justify-center items-center text-center',
                                    selectedPaymentMethod === 'cash'
                                        ? 'bg-yellow-500 font-bold'
                                        : 'text-black',
                                ]">
                                    <img src="/images/money-stack.png" alt="" class="w-24" />
                                </div>
                                <div @click="selectedPaymentMethod = 'card'" :class="[
                                    'cursor-pointer w-[100px] border border-black rounded-xl flex flex-col justify-center items-center text-center',
                                    selectedPaymentMethod === 'card'
                                        ? 'bg-yellow-500 font-bold'
                                        : 'text-black',
                                ]">
                                    <img src="/images/bank-card.png" alt="" class="w-24" />
                                </div>
                            </div>

                            <div class="flex items-center justify-center w-full">
                                <button @click="() => {
                                    submitOrder();
                                }
                                    " type="button" :disabled="products.length === 0" :class="[
                                        'w-full bg-black py-4 text-2xl font-bold tracking-wider text-center text-white uppercase rounded-xl',
                                        products.length === 0
                                            ? ' cursor-not-allowed'
                                            : ' cursor-pointer',
                                    ]">
                                    <i class="pr-4 ri-add-circle-fill"></i> Confirm Order
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Products Tab -->

            <!-- SIM Activation Tab Content -->
            <div v-show="activeTab === 'sim'" class="bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">📱 SIM Activation Transaction</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Transaction Form -->
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Operator *</label>
                            <select 
                                v-model="simForm.operator_name"
                                @change="onSimOperatorChange"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Choose operator...</option>
                                <option v-for="operator in uniqueSimOperators" :key="operator.id" :value="operator.name">
                                    {{ operator.name }}
                                </option>
                            </select>
                        </div>

                        <div v-if="simForm.operator_name">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Select SIM (Optional - for activation)
                            </label>
                            <select 
                                v-model="simForm.sim_stock_id"
                                @change="onSimChange"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">No SIM (Reload only)</option>
                                <option v-for="sim in filteredSimStocks" :key="sim.id" :value="sim.id">
                                    {{ sim.sim_name }} (Stock: {{ sim.stock }})
                                </option>
                            </select>
                        </div>

                        <div v-if="simForm.operator_name">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Package *</label>
                            <select 
                                v-model="simForm.pricing_rule_id"
                                @change="getSimPreview"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Choose package...</option>
                                <option v-for="rule in availableSimPackages" :key="rule.id" :value="rule.id">
                                    Rs. {{ formatCurrency(rule.face_value) }}
                                    <template v-if="rule.seller_discount_percent > 0"> - {{ rule.seller_discount_percent }}% discount</template>
                                    <template v-if="rule.extra_benefit > 0"> + Rs. {{ rule.extra_benefit }} benefit</template>
                                </option>
                            </select>
                        </div>

                        <div v-if="simForm.pricing_rule_id">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mobile Number *</label>
                            <input 
                                v-model="simForm.mobile_number"
                                @input="mobileNumberError = ''"
                                type="text"
                                pattern="[0-9]{10}"
                                maxlength="10"
                                required
                                placeholder="07XXXXXXXX"
                                :class="[
                                    'w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500',
                                    mobileNumberError ? 'border-red-500 bg-red-50' : 'border-gray-300'
                                ]"
                            />
                            <p v-if="mobileNumberError" class="text-xs text-red-600 mt-1">{{ mobileNumberError }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea 
                                v-model="simForm.notes"
                                rows="3"
                                placeholder="Optional notes..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Billing Details -->
                    <div class="bg-white rounded-lg p-6 shadow-lg h-fit">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">Billing Details</h3>
                        
                        <div v-if="simPreview">
                            <!-- SIM Operator Name -->
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <p class="text-sm text-gray-500 mb-1">Operator</p>
                                <p class="text-lg font-bold text-gray-900">{{ simForm.operator_name }}</p>
                            </div>
                            
                            <!-- Package Details -->
                            <div v-if="selectedSimPackage" class="mb-4 pb-4 border-b border-gray-200">
                                <p class="text-sm text-gray-500 mb-1">Package</p>
                                <p class="text-lg font-bold text-gray-900">
                                    Rs. {{ formatCurrency(selectedSimPackage.face_value) }}
                                </p>
                            </div>
                            
                            <!-- SIM Price (if SIM is included) -->
                            <div v-if="simPreview.sim_revenue && simPreview.sim_revenue > 0" class="mb-4 pb-4 border-b border-gray-200">
                                <p class="text-sm text-gray-500 mb-1">SIM Card</p>
                                <p class="text-lg font-bold text-gray-900">
                                    Rs. {{ formatCurrency(simPreview.sim_revenue) }}
                                </p>
                            </div>
                            
                            <!-- Total Amount -->
                            <div class="flex justify-between items-center py-4 border-b-2 border-gray-200">
                                <span class="text-xl font-semibold text-gray-700">Total</span>
                                <span class="text-3xl font-bold text-gray-900">Rs. {{ formatCurrency(calculateSimTotalWithSurcharge()) }}</span>
                            </div>
                            
                            <!-- Payment Method Selection -->
                            <div class="mt-6 mb-4">
                                <div class="flex items-center justify-start space-x-4 mb-3">
                                    <p class="text-xl font-medium text-black">Payment Method :</p>
                                </div>
                                <div class="flex justify-start space-x-4">
                                    <div 
                                        @click="simForm.payment_method = 'cash'" 
                                        :class="[
                                            'cursor-pointer border-2 border-black rounded-xl flex flex-col justify-center items-center p-4 transition-all',
                                            simForm.payment_method === 'cash'
                                                ? 'bg-yellow-500'
                                                : 'bg-white'
                                        ]"
                                    >
                                        <img src="/images/money-stack.png" alt="Cash" class="w-20 h-20" />
                                    </div>
                                    <div 
                                        @click="simForm.payment_method = 'card'" 
                                        :class="[
                                            'cursor-pointer border-2 border-black rounded-xl flex flex-col justify-center items-center p-4 transition-all',
                                            simForm.payment_method === 'card'
                                                ? 'bg-yellow-500'
                                                : 'bg-white'
                                        ]"
                                    >
                                        <img src="/images/bank-card.png" alt="Card" class="w-20 h-20" />
                                    </div>
                                </div>
                                <p v-if="simForm.payment_method === 'card'" class="text-xs text-orange-600 mt-2 font-semibold">
                                    +2.5% surcharge applies
                                </p>
                            </div>
                            
                            <!-- Cash Received Input -->
                            <div class="mb-4">
                                <label class="block text-base font-medium text-gray-700 mb-2">Cash Received</label>
                                <input 
                                    v-model="simForm.cash_received"
                                    type="number"
                                    step="0.01"
                                    placeholder="Enter amount received from customer"
                                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg font-semibold"
                                />
                            </div>
                            
                            <!-- Balance/Change Display -->
                            <div v-if="simForm.cash_received" class="py-3 border-t-2 border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-base font-medium text-gray-700">Balance</span>
                                    <span class="text-2xl font-bold" :class="calculateSimChange() >= 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ calculateSimChange() >= 0 ? '' : '-' }}Rs. {{ formatCurrency(Math.abs(calculateSimChange())) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div v-else class="text-center text-gray-500 py-8">
                            <p>Select package to see billing details</p>
                        </div>
                        
                        <button 
                            @click="processSimActivation"
                            :disabled="!simForm.operator_name || !simForm.pricing_rule_id || !simForm.mobile_number || simProcessing"
                            :class="[
                                'w-full mt-6 py-4 text-xl font-bold text-white rounded-xl transition-all',
                                (!simForm.operator_name || !simForm.pricing_rule_id || !simForm.mobile_number || simProcessing)
                                    ? 'bg-gray-400 cursor-not-allowed'
                                    : 'bg-green-600 hover:bg-green-700'
                            ]"
                        >
                            <i class="ri-check-line mr-2"></i> 
                            {{ simProcessing ? 'Processing...' : (simForm.sim_stock_id ? 'Activate SIM' : 'Process Reload') }}
                        </button>
                    </div>
                </div>
            </div>
            <!-- End SIM Tab -->

            <!-- Reload Tab Content -->
            <div v-show="activeTab === 'reload'" class="bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">💰 Sell Reload</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Reload Form -->
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Operator *</label>
                            <select 
                                v-model="reloadForm.operator_id"
                                @change="loadReloadOperatorData"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Choose operator...</option>
                                <option v-for="operator in operators" :key="operator.id" :value="operator.id">
                                    {{ operator.name }} - Balance: Rs. {{ formatCurrency(getWalletBalance(operator.id)) }}
                                </option>
                            </select>
                        </div>

                        <div v-if="reloadForm.operator_id">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Package *</label>
                            <select 
                                v-model="reloadForm.reload_package_id"
                                @change="getReloadQuote"
                                required
                                :disabled="reloadPackagesLoading"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100"
                            >
                                <option value="">{{ reloadPackagesLoading ? 'Loading packages...' : 'Choose package...' }}</option>
                                <option v-for="pkg in reloadPackages" :key="pkg.id" :value="pkg.id">
                                    {{ pkg.name }} - Rs. {{ formatCurrency(pkg.face_value) }}
                                </option>
                            </select>
                            <p v-if="reloadPackages.length === 0 && !reloadPackagesLoading && reloadForm.operator_id" class="text-xs text-red-500 mt-1">
                                No packages available for this operator
                            </p>
                        </div>

                        <div v-if="reloadForm.operator_id">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mobile Number (MSISDN) *</label>
                            <input 
                                v-model="reloadForm.msisdn"
                                type="text"
                                pattern="[0-9]{10}"
                                maxlength="10"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="07XXXXXXXX"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea 
                                v-model="reloadForm.notes"
                                rows="3"
                                placeholder="Optional notes..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Billing Details -->
                    <div class="bg-white rounded-lg p-6 shadow-lg h-fit">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">Billing Details</h3>
                        
                        <div v-if="reloadQuote">
                            <!-- Operator Name -->
                            <div v-if="selectedReloadOperator" class="mb-4 pb-4 border-b border-gray-200">
                                <p class="text-sm text-gray-500 mb-1">Operator</p>
                                <p class="text-lg font-bold text-gray-900">{{ selectedReloadOperator.name }}</p>
                            </div>
                            
                            <!-- Package Details -->
                            <div v-if="selectedReloadPackage" class="mb-4 pb-4 border-b border-gray-200">
                                <p class="text-sm text-gray-500 mb-1">Package</p>
                                <p class="text-lg font-bold text-gray-900">
                                    {{ selectedReloadPackage.name }} - Rs. {{ formatCurrency(selectedReloadPackage.face_value) }}
                                </p>
                            </div>
                            
                            <!-- Total Amount -->
                            <div class="flex justify-between items-center py-4 border-b-2 border-gray-200">
                                <span class="text-xl font-semibold text-gray-700">Total</span>
                                <span class="text-3xl font-bold text-gray-900">Rs. {{ formatCurrency(calculateReloadTotalWithSurcharge()) }}</span>
                            </div>
                            
                            <!-- Payment Method Selection -->
                            <div class="mt-6 mb-4">
                                <div class="flex items-center justify-start space-x-4 mb-3">
                                    <p class="text-xl font-medium text-black">Payment Method :</p>
                                </div>
                                <div class="flex justify-start space-x-4">
                                    <div 
                                        @click="reloadForm.payment_method = 'cash'" 
                                        :class="[
                                            'cursor-pointer border-2 border-black rounded-xl flex flex-col justify-center items-center p-4 transition-all',
                                            reloadForm.payment_method === 'cash'
                                                ? 'bg-yellow-500'
                                                : 'bg-white'
                                        ]"
                                    >
                                        <img src="/images/money-stack.png" alt="Cash" class="w-20 h-20" />
                                    </div>
                                    <div 
                                        @click="reloadForm.payment_method = 'card'" 
                                        :class="[
                                            'cursor-pointer border-2 border-black rounded-xl flex flex-col justify-center items-center p-4 transition-all',
                                            reloadForm.payment_method === 'card'
                                                ? 'bg-yellow-500'
                                                : 'bg-white'
                                        ]"
                                    >
                                        <img src="/images/bank-card.png" alt="Card" class="w-20 h-20" />
                                    </div>
                                </div>
                                <p v-if="reloadForm.payment_method === 'card'" class="text-xs text-orange-600 mt-2 font-semibold">
                                    +2.5% surcharge applies
                                </p>
                            </div>
                            
                            <!-- Cash Received Input -->
                            <div class="mb-4">
                                <label class="block text-base font-medium text-gray-700 mb-2">Cash Received</label>
                                <input 
                                    v-model="reloadForm.cash_received"
                                    type="number"
                                    step="0.01"
                                    placeholder="Enter amount received from customer"
                                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg font-semibold"
                                />
                            </div>
                            
                            <!-- Balance/Change Display -->
                            <div v-if="reloadForm.cash_received" class="py-3 border-t-2 border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-base font-medium text-gray-700">Balance</span>
                                    <span class="text-2xl font-bold" :class="calculateReloadChange() >= 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ calculateReloadChange() >= 0 ? '' : '-' }}Rs. {{ formatCurrency(Math.abs(calculateReloadChange())) }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Insufficient Balance Warning -->
                            <div v-if="!reloadQuote.sufficient_balance" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-sm text-red-800 font-semibold">⚠️ Insufficient wallet balance! Please deposit first.</p>
                            </div>
                        </div>
                        
                        <div v-else class="text-center text-gray-500 py-8">
                            <p>Select package to see billing details</p>
                        </div>
                        
                        <button 
                            @click="processSellReload"
                            :disabled="!reloadForm.operator_id || !reloadForm.reload_package_id || !reloadForm.msisdn || reloadProcessing || !reloadQuote?.sufficient_balance"
                            :class="[
                                'w-full mt-6 py-4 text-xl font-bold text-white rounded-xl transition-all',
                                (!reloadForm.operator_id || !reloadForm.reload_package_id || !reloadForm.msisdn || reloadProcessing || !reloadQuote?.sufficient_balance)
                                    ? 'bg-gray-400 cursor-not-allowed'
                                    : 'bg-green-600 hover:bg-green-700'
                            ]"
                        >
                            <i class="ri-check-line mr-2"></i> 
                            {{ reloadProcessing ? 'Processing...' : 'Process Reload Sale' }}
                        </button>
                    </div>
                </div>
            </div>
            <!-- End Reload Tab -->

        </div>
    </div>
    
    <!-- Success Modals -->
    <SimActivationSuccessModal
        v-if="completedSimTransaction"
        :open="showSimSuccessModal"
        @update:open="showSimSuccessModal = $event"
        :transaction="completedSimTransaction"
        :cashier="loggedInUser"
    />
    <ReloadSuccessModal
        v-if="completedReloadTransaction"
        :open="showReloadSuccessModal"
        @update:open="showReloadSuccessModal = $event"
        :reloadSale="completedReloadTransaction"
        :cashier="loggedInUser"
    />
    <PosSuccessModel :open="isSuccessModalOpen" @update:open="handleModalOpenUpdate" :products="products"
        :employee="employee" :cashier="loggedInUser" :customer="customer" :orderid="orderid" :cash="cash"
        :balance="balance" :subTotal="subtotal" :totalDiscount="totalDiscount" :total="total"
        :custom_discount_type="custom_discount_type"
        :custom_discount="custom_discount" />
    <AlertModel v-model:open="isAlertModalOpen" :message="message" />

    <SelectProductModel
      v-model:open="isSelectProductModalOpen"
      :allcategories="allcategories"
      :colors="colors"
      :sizes="sizes"
      @selected-products="handleSelectedProducts"
    />
    <SelectNewspaperModel
      v-model:open="isSelectNewspaperModalOpen"
      @import-newspapers="handleImportedNewspapers"
    />
    <SelectPhotocopyModel
      v-model:open="isSelectPhotocopyModalOpen"
      @import-photocopies="handleImportedPhotocopies"
    />
    <SelectPrintoutModel
      v-model:open="isSelectPrintoutModalOpen"
      @import-printouts="handleImportedPrintouts"
    />
    <SelectLaminatingModal
      v-model:open="isSelectLaminatingModalOpen"
      @import-laminatings="handleImportedLaminatings"
    />
    <SelectBindingModel
      v-model:open="isSelectBindingModalOpen"
      @import-bindings="handleImportedBindings"
    />
    <Footer />
</template>
<script setup>
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import PosSuccessModel from "@/Components/custom/PosSuccessModel.vue";
import AlertModel from "@/Components/custom/AlertModel.vue";
import SimActivationSuccessModal from "@/Components/custom/SimActivationSuccessModal.vue";
import ReloadSuccessModal from "@/Components/custom/ReloadSuccessModal.vue";
import { useForm, router } from "@inertiajs/vue3";
import { ref, onMounted, computed, watch, defineAsyncComponent } from 'vue';
import { Head } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import axios from "axios";
import CurrencyInput from "@/Components/custom/CurrencyInput.vue";
import SelectProductModel from "@/Components/custom/SelectProductModel.vue";
import ProductAutoComplete from "@/Components/custom/ProductAutoComplete.vue";
import { generateOrderId } from "@/Utils/Other.js";
import SelectNewspaperModel from "@/Components/custom/SelectNewspaperModel.vue";
import SelectPrintoutModel from '@/Components/custom/SelectPrintoutModel.vue';
import SelectLaminatingModal from '@/Components/custom/SelectLaminatingModal.vue';
import SelectBindingModel from '@/Components/custom/SelectBindingModel.vue';

// Use dynamic import for SelectPhotocopyModel
const SelectPhotocopyModel = defineAsyncComponent(() => 
  import("@/Components/custom/SelectPhotocopyModel.vue")
);

// Tab Management
const activeTab = ref('products');

// Existing POS variables
const product = ref(null);
const error = ref(null);
const products = ref([]);
const isSuccessModalOpen = ref(false);
const isAlertModalOpen = ref(false);
const message = ref("");
const appliedCoupon = ref(null);
const cash = ref(0);
const custom_discount = ref(0);
const isSelectModalOpen = ref(false);
const custom_discount_type = ref('percent');
const orderid = computed(() => generateOrderId());

// SIM Activation variables
const showSimSuccessModal = ref(false);
const completedSimTransaction = ref(null);
const simProcessing = ref(false);
const simPreview = ref(null);
const mobileNumberError = ref('');
const simForm = ref({
    operator_name: '',
    sim_stock_id: '',
    pricing_rule_id: '',
    mobile_number: '',
    notes: '',
    package_revenue: null,
    sim_cost: null,
    sim_revenue: null,
    payment_method: 'cash',
    cash_received: '',
});

// Reload variables
const showReloadSuccessModal = ref(false);
const completedReloadTransaction = ref(null);
const reloadProcessing = ref(false);
const reloadPackages = ref([]);
const reloadPackagesLoading = ref(false);
const reloadQuote = ref(null);
const reloadForm = ref({
    operator_id: null,
    reload_package_id: '',
    msisdn: '',
    notes: '',
    payment_method: 'cash',
    cash_received: '',
});

// Computed properties for SIM & Reload
const uniqueSimOperators = computed(() => {
    const unique = [];
    const seen = new Set();
    props.simStocks.forEach(sim => {
        if (!seen.has(sim.sim_name)) {
            seen.add(sim.sim_name);
            unique.push({ id: sim.id, name: sim.sim_name });
        }
    });
    return unique;
});

const filteredSimStocks = computed(() => {
    if (!simForm.value.operator_name) return [];
    return props.simStocks.filter(sim => 
        sim.sim_name === simForm.value.operator_name && sim.stock > 0
    );
});

const availableSimPackages = computed(() => {
    if (!simForm.value.operator_name) return [];
    return props.pricingRules.filter(rule => 
        rule.operator_name === simForm.value.operator_name && rule.is_active
    );
});

const selectedSimPackage = computed(() => {
    if (!simForm.value.pricing_rule_id) return null;
    return props.pricingRules.find(rule => rule.id === simForm.value.pricing_rule_id);
});

const selectedReloadOperator = computed(() => {
    if (!reloadForm.value.operator_id) return null;
    return props.operators.find(op => op.id === reloadForm.value.operator_id);
});

const selectedReloadPackage = computed(() => {
    if (!reloadForm.value.reload_package_id) return null;
    return reloadPackages.value.find(pkg => pkg.id === reloadForm.value.reload_package_id);
});

const getWalletBalance = (operatorId) => {
    const wallet = props.wallets[operatorId];
    return wallet ? wallet.balance : 0;
};


// const balance = ref(0);

const handleModalOpenUpdate = (newValue) => {
    isSuccessModalOpen.value = newValue;
    if (!newValue) {
        refreshData();
    }
};

const props = defineProps({
    loggedInUser: Object,
    allcategories: Array,
    allemployee: Array,
    colors: Array,
    sizes: Array,
    // SIM & Reload data from database
    simStocks: Array,
    operators: Array,
    wallets: Object,
    pricingRules: Array,
});

const discount = ref(0);

const customer = ref({
    name: "",
    countryCode: "",
    contactNumber: "",
    email: "",
});

const employee_id = ref("");

const selectedPaymentMethod = ref("cash");
const selectedManualType = ref("products");
const isSelectProductModalOpen = ref(false);
const isDropdownOpen = ref(false);

const dropdownOptions = ref([
  { value: 'products', label: 'Products', icon: '🛍️' },
  { value: 'newspapers', label: 'Newspapers', icon: '📰' },
  { value: 'photocopy', label: 'Photocopy', icon: '📄' },
  { value: 'printout', label: 'Printout', icon: '🖨️' },
  { value: 'binding', label: 'Binding', icon: '📖' },
  { value: 'Laminating', label: 'Laminating', icon: '🛡️' }
]);
const isSelectNewspaperModalOpen = ref(false);
const isSelectPhotocopyModalOpen = ref(false);
const isSelectPrintoutModalOpen = ref(false);
const isSelectLaminatingModalOpen = ref(false);
const isSelectBindingModalOpen = ref(false);

const refreshData = () => {
    router.visit(route("pos.index"), {
        preserveScroll: false, // Reset scroll
        preserveState: false, // Reset component state
    });
};

const removeProduct = (id) => {
    products.value = products.value.filter((item) => item.id !== id);
};

const removeCoupon = () => {
    appliedCoupon.value = null; // Clear the applied coupon
    couponForm.code = ""; // Clear the coupon field
};

const incrementQuantity = (id) => {
    const product = products.value.find((item) => item.id === id);
    if (product) {
        product.quantity += 1;
    }
};

const decrementQuantity = (id) => {
    const product = products.value.find((item) => item.id === id);
    if (product && product.quantity > 1) {
        product.quantity -= 1;
    }
};

// const orderId = computed(() => {
//   const timestamp = Date.now().toString(36).toUpperCase(); // Convert timestamp to a base-36 string
//   const randomString = Math.random().toString(36).substr(2, 5).toUpperCase(); // Generate a shorter random string
//   return `ORD-${timestamp}-${randomString}`; // Combine to create unique order ID
// });
const orderId = computed(() => {
    const characters =
        "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    return Array.from({ length: 6 }, () =>
        characters.charAt(Math.floor(Math.random() * characters.length))
    ).join("");
});

const submitOrder = async () => {
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
            orderid: orderid.value,
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
// };

const subtotal = computed(() => {
    return products.value
        .reduce(
            (total, item) => total + parseFloat(item.selling_price) * item.quantity,
            0
        )
        .toFixed(2); // Ensures two decimal places
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

    const couponDiscount = appliedCoupon.value
        ? Number(appliedCoupon.value.discount)
        : 0;

    return (productDiscount + couponDiscount).toFixed(2);
});

const validateCustomDiscount = () => {
    if (!custom_discount.value || isNaN(custom_discount.value)) {
        custom_discount.value = 0; // Set default to 0 if the field is empty or invalid
    }
};

const total = computed(() => {
    const subtotalValue = parseFloat(subtotal.value) || 0;
    const discountValue = parseFloat(totalDiscount.value) || 0;
    const customDiscount = parseFloat(custom_discount.value) || 0;

    let customValue = 0;

    if (custom_discount_type.value === 'percent') {
        customValue = (subtotalValue * customDiscount) / 100;
    } else if (custom_discount_type.value === 'fixed') {
        customValue = customDiscount;
    }

    return (subtotalValue - discountValue - customValue).toFixed(2);
});

const balance = computed(() => {
    if (cash.value == null || cash.value === 0) {
        return 0; // If cash.value is null or 0, return 0
    }
    return (parseFloat(cash.value) - parseFloat(total.value)).toFixed(2);
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

// Automatically submit the barcode to the backend
const submitBarcode = async () => {
    try {
        // Send POST request to the backend
        const response = await axios.post(route("pos.getProduct"), {
            barcode: form.barcode, // Send the barcode field
        });

        // Extract the response data
        const { product: fetchedProduct, error: fetchedError } = response.data;

        if (fetchedProduct) {
            if (fetchedProduct.stock_quantity < 1) {
                isAlertModalOpen.value = true;
                message.value = "Product is out of stock";
                return;
            }
            // Check if the product already exists in the products array
            const existingProduct = products.value.find(
                (item) => item.id === fetchedProduct.id
            );

            if (existingProduct) {
                // If it exists, increment the quantity
                existingProduct.quantity += 1;
            } else {
                // If it doesn't exist, add it to the products array with quantity 1
                products.value.push({
                    ...fetchedProduct,
                    quantity: 1,
                    apply_discount: false, // Add the new attribute
                });
            }

            product.value = fetchedProduct; // Update product state for individual display
            error.value = null; // Clear any previous errors
            console.log(
                "Product fetched successfully and added to cart:",
                fetchedProduct
            );
        } else {
            isAlertModalOpen.value = true;
            message.value = fetchedError;
            error.value = fetchedError; // Set the error message
            console.error("Error:", fetchedError);
        }
    } catch (err) {
        if (err.response.status === 422) {
            isAlertModal.value = true;
            message.value = err.response.data.message;
        }

        console.error("An error occurred:", err.response?.data || err.message);
        error.value = "An unexpected error occurred. Please try again.";
    }
};

// Handle input from the barcode scanner
const handleScannerInput = (event) => {
    clearTimeout(timeout); // Clear the timeout for each keypress
    if (event.key === "Enter") {
        // Barcode scanning completed
        form.barcode = barcode; // Set the scanned barcode into the form
        submitBarcode(); // Automatically submit the barcode
        barcode = ""; // Reset the barcode for the next scan
    } else {
        // Append the pressed key to the barcode
        barcode += event.key;
    }

    // Timeout to reset the barcode if scanning is interrupted
    timeout = setTimeout(() => {
        barcode = "";
    }, 100); // Adjust timeout based on scanner speed
};

// Handle click outside dropdown to close it
const handleClickOutside = (event) => {
  const dropdown = event.target.closest('.relative');
  if (!dropdown) {
    isDropdownOpen.value = false;
  }
};

// Attach the keypress event listener when the component is mounted
onMounted(() => {
    document.addEventListener("keypress", handleScannerInput);
    document.addEventListener("click", handleClickOutside);
    console.log(props.products);
});

const applyDiscount = (id) => {
    products.value.forEach((product) => {
        if (product.id === id) {
            product.apply_discount = true;
        }
    });
};

const removeDiscount = (id) => {
    products.value.forEach((product) => {
        if (product.id === id) {
            product.apply_discount = false;
        }
    });
};

const handleSelectedProducts = (selectedProducts) => {
    selectedProducts.forEach((fetchedProduct) => {
        const existingProduct = products.value.find(
            (item) => item.id === fetchedProduct.id
        );

        if (existingProduct) {
            // If the product exists, increment its quantity
            existingProduct.quantity += 1;
        } else {
            // If the product doesn't exist, add it with a default quantity
            products.value.push({
                ...fetchedProduct,
                quantity: 1,
                apply_discount: false, // Default additional attribute
            });
        }
    });
};

const selectOption = (optionValue) => {
  selectedManualType.value = optionValue;
  isDropdownOpen.value = false;
  openModalBasedOnType();
};

const openModalBasedOnType = () => {
  if (selectedManualType.value === "binding") {
    isSelectBindingModalOpen.value = true;
  } else if (selectedManualType.value === "photocopy") {
    isSelectPhotocopyModalOpen.value = true;
  } else if (selectedManualType.value === "newspapers") {
    isSelectNewspaperModalOpen.value = true;
  } else if (selectedManualType.value === "printout") {
    isSelectPrintoutModalOpen.value = true;
  } else if (selectedManualType.value === "Laminating") {
    isSelectLaminatingModalOpen.value = true;
  } else {
    isSelectProductModalOpen.value = true;
  }
};

const openManualModal = () => {
  if (selectedManualType.value === "photocopy") {
    isSelectPhotocopyModalOpen.value = true;
  } else if (selectedManualType.value === "newspapers") {
    isSelectNewspaperModalOpen.value = true;
  } else if (selectedManualType.value === "printout") {
    isSelectPrintoutModalOpen.value = true;
  } else if (selectedManualType.value === "Laminating") {
    isSelectLaminatingModalOpen.value = true;
  } else {
    isSelectProductModalOpen.value = true;
  }
};

const handleSelectedNewspaper = (newspaper) => {
  console.log("Selected newspaper:", newspaper);
  // Add logic to handle the selected newspaper
};

const handleImportedNewspapers = (newspapers) => {
  newspapers.forEach((newspaper) => {
    const existingItem = products.value.find((item) => item.id === newspaper.id && item.is_newspaper === true);
    if (existingItem) {
      existingItem.quantity += 1;
    } else {
      products.value.push({
        id: newspaper.id,
        name: newspaper.name,
        barcode: newspaper.barcode,
        selling_price: newspaper.selling_price,
        cost_price: newspaper.cost_price ?? 0,
        stock_quantity: newspaper.stock_quantity,
        discount: newspaper.discount ?? 0,
        discounted_price: newspaper.discount_price ?? newspaper.selling_price,
        is_newspaper: true,  // This is critical!
        image: null,
        quantity: 1,
        apply_discount: false,
      });
    }
  });
};

const handleImportedPhotocopies = (photocopies) => {
  photocopies.forEach((photocopy) => {
    const existingItem = products.value.find(
      (item) => item.id === photocopy.id && item.is_photocopy === true
    );
    if (existingItem) {
      existingItem.quantity += 1;
    } else {
      products.value.push({
        ...photocopy,
        quantity: 1,
        is_photocopy: true,
        selling_price: photocopy.totalprice, // Ensure selling_price is included
      });
    }
  });
};

const handleImportedPrintouts = (printouts) => {
  printouts.forEach((printout) => {
    const existingItem = products.value.find(
      (item) => item.id === printout.id && item.is_printout === true
    );
    if (existingItem) {
      existingItem.quantity += 1;
    } else {
      products.value.push({
        ...printout,
        quantity: 1,
        is_printout: true,
        selling_price: printout.totalprice, // Ensure selling_price is included
      });
    }
  });
};

const handleImportedLaminatings = (laminatings) => {
  laminatings.forEach((laminating) => {
    const existingItem = products.value.find(
      (item) => item.id === laminating.id && item.is_laminating === true
    );
    if (existingItem) {
      existingItem.quantity += 1;
    } else {
      products.value.push({
        ...laminating,
        quantity: 1,
        is_laminating: true,
        name: laminating.name, // Ensure name is properly set
        selling_price: laminating.selling_price, // Use the selling_price from API
        cost_price: laminating.cost_price || laminating.price, // Fallback to price if cost_price not available
        discount: 0,
        discounted_price: laminating.selling_price,
      });
    }
  });
};

const handleImportedBindings = (bindings) => {
  bindings.forEach((binding) => {
    const existingItem = products.value.find(
      (item) => item.id === binding.id && item.is_binding === true
    );
    if (existingItem) {
      existingItem.quantity += 1;
    } else {
      products.value.push({
        ...binding,
        quantity: 1,
        is_binding: true,
        name: binding.name, // Ensure name is properly set
        selling_price: binding.selling_price, // Use the selling_price from API
        cost_price: binding.cost_price || binding.price, // Fallback to price if cost_price not available
        discount: 0,
        discounted_price: binding.selling_price,
      });
    }
  });
};

const photocopyTotalPrice = computed(() => {
    return products.value
        .filter((item) => item.is_photocopy)
        .reduce((total, item) => total + item.totalprice * item.quantity, 0);
});

const generateReceipt = () => {
    const receiptDetails = products.value.map((item) => ({
        name: item.name,
        quantity: item.quantity,
        unit_price: item.selling_price,
        total_price: item.selling_price * item.quantity,
        type: item.is_photocopy ? 'photocopy' : 
              item.is_printout ? 'printout' : 
              item.is_laminating ? 'laminating' : 
              item.is_binding ? 'binding' :
              item.is_newspaper ? 'newspaper' : 'product'
    }));

    console.log("Receipt Details:", receiptDetails);
    return receiptDetails;
};

// Helper function for currency formatting
const formatCurrency = (amount) => {
    return parseFloat(amount || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
};

// Helper functions for card surcharge calculations
const calculateSimCardSurcharge = () => {
    if (!simPreview.value) return 0;
    const baseAmount = simPreview.value.customer_payment || simPreview.value.total_revenue || 0;
    return baseAmount * 0.025; // 2.5%
};

const calculateSimTotalWithSurcharge = () => {
    if (!simPreview.value) return 0;
    const base = simPreview.value.customer_payment || simPreview.value.total_revenue || 0;
    return simForm.value.payment_method === 'card' ? base + calculateSimCardSurcharge() : base;
};

const calculateReloadCardSurcharge = () => {
    if (!reloadQuote.value || !reloadQuote.value.face_value) return 0;
    return reloadQuote.value.face_value * 0.025; // 2.5%
};

const calculateReloadTotalWithSurcharge = () => {
    if (!reloadQuote.value || !reloadQuote.value.face_value) return 0;
    const base = reloadQuote.value.face_value;
    return reloadForm.value.payment_method === 'card' ? base + calculateReloadCardSurcharge() : base;
};

// Helper functions for change calculation
const calculateSimChange = () => {
    const cashReceived = parseFloat(simForm.value.cash_received) || 0;
    const total = calculateSimTotalWithSurcharge();
    return cashReceived - total;
};

const calculateReloadChange = () => {
    const cashReceived = parseFloat(reloadForm.value.cash_received) || 0;
    const total = calculateReloadTotalWithSurcharge();
    return cashReceived - total;
};

// ==================== SIM ACTIVATION FUNCTIONS ====================
const onSimOperatorChange = () => {
    simForm.value.sim_stock_id = '';
    simForm.value.pricing_rule_id = '';
    simPreview.value = null;
};

const onSimChange = () => {
    // Auto-populate SIM cost and revenue from stock when SIM is selected
    if (simForm.value.sim_stock_id) {
        const selectedSim = props.simStocks.find(sim => sim.id == simForm.value.sim_stock_id);
        if (selectedSim) {
            simForm.value.sim_cost = parseFloat(selectedSim.cost_price || 0);
            simForm.value.sim_revenue = parseFloat(selectedSim.selling_price || 0);
        }
    } else {
        simForm.value.sim_cost = null;
        simForm.value.sim_revenue = null;
    }
    getSimPreview();
};

const getSimPreview = async () => {
    if (!simForm.value.pricing_rule_id) {
        simPreview.value = null;
        return;
    }

    try {
        const response = await axios.post('/api/sim-activation/preview', simForm.value);
        if (response.data.success) {
            simPreview.value = response.data.preview;
        }
    } catch (error) {
        console.error('Preview failed:', error);
    }
};

const processSimActivation = async () => {
    simProcessing.value = true;
    mobileNumberError.value = ''; // Clear any previous errors
    
    try {
        // Calculate card surcharge if applicable
        const cardSurcharge = simForm.value.payment_method === 'card' ? calculateSimCardSurcharge() : 0;
        const changeAmount = calculateSimChange();
        
        // Use the existing SIM activation route (POST to /api/sim-activation/)
        const response = await axios.post('/api/sim-activation', {
            ...simForm.value,
            card_surcharge: cardSurcharge,
            change_amount: changeAmount >= 0 ? changeAmount : 0,
        });

        if (response.data.success) {
            completedSimTransaction.value = response.data.transaction;
            showSimSuccessModal.value = true;
            
            // Reset form
            simForm.value = {
                operator_name: '',
                sim_stock_id: '',
                pricing_rule_id: '',
                mobile_number: '',
                notes: '',
                package_revenue: null,
                sim_cost: null,
                sim_revenue: null,
                payment_method: 'cash',
                cash_received: '',
            };
            simPreview.value = null;
            
            isAlertModalOpen.value = true;
            message.value = 'SIM activation processed successfully!';
        }
    } catch (err) {
        console.error('SIM Activation Error:', err);
        
        // Check for duplicate mobile number error
        if (err.response?.status === 422 && err.response?.data?.errors?.mobile_number) {
            mobileNumberError.value = `This number has already been activated`;
            message.value = `Mobile number ${simForm.value.mobile_number} has already been used for SIM activation.`;
        } else {
            message.value = err.response?.data?.message || 'Failed to process SIM activation';
        }
        
        isAlertModalOpen.value = true;
    } finally {
        simProcessing.value = false;
    }
};

// ==================== RELOAD FUNCTIONS ====================
const loadReloadOperatorData = async () => {
    // Reset form when operator changes
    reloadForm.value.reload_package_id = '';
    reloadQuote.value = null;
    
    if (!reloadForm.value.operator_id) {
        reloadPackages.value = [];
        return;
    }
    
    // Load packages for selected operator
    reloadPackagesLoading.value = true;
    try {
        const response = await axios.get(`/api/wallet/packages?operator_id=${reloadForm.value.operator_id}`);
        reloadPackages.value = response.data.packages || [];
    } catch (error) {
        console.error('Failed to load packages:', error);
        reloadPackages.value = [];
    } finally {
        reloadPackagesLoading.value = false;
    }
};

const getReloadQuote = async () => {
    if (!reloadForm.value.reload_package_id) {
        reloadQuote.value = null;
        return;
    }
    
    try {
        const response = await axios.post('/api/wallet/quote', {
            operator_id: reloadForm.value.operator_id,
            reload_package_id: reloadForm.value.reload_package_id,
        });
        
        if (response.data.success) {
            reloadQuote.value = response.data.quote;
        }
    } catch (error) {
        console.error('Failed to get quote:', error);
        isAlertModalOpen.value = true;
        message.value = 'Failed to get quote: ' + (error.response?.data?.message || error.message);
    }
};

const processSellReload = async () => {
    if (!reloadQuote.value?.sufficient_balance) {
        isAlertModalOpen.value = true;
        message.value = 'Insufficient wallet balance!';
        return;
    }

    reloadProcessing.value = true;
    try {
        // Calculate card surcharge if applicable
        const cardSurcharge = reloadForm.value.payment_method === 'card' ? calculateReloadCardSurcharge() : 0;
        const changeAmount = calculateReloadChange();
        
        // Use the existing wallet sell route
        const response = await axios.post('/api/wallet/sell', {
            ...reloadForm.value,
            card_surcharge: cardSurcharge,
            change_amount: changeAmount >= 0 ? changeAmount : 0,
        });

        if (response.data.success) {
            completedReloadTransaction.value = response.data.reloadSale;
            showReloadSuccessModal.value = true;
            
            // Reset form
            reloadForm.value = {
                operator_id: null,
                reload_package_id: '',
                msisdn: '',
                notes: '',
                payment_method: 'cash',
                cash_received: '',
            };
            reloadQuote.value = null;
            reloadPackages.value = [];
            
            isAlertModalOpen.value = true;
            message.value = 'Reload sold successfully!';
        }
    } catch (err) {
        console.error('Reload Sale Error:', err);
        isAlertModalOpen.value = true;
        message.value = err.response?.data?.message || 'Failed to process reload sale';
    } finally {
        reloadProcessing.value = false;
    }
};

// const searchTerm = ref(form.barcode);

// // Computed property for filtered product results
// const searchResults = computed(() => {
//   if (searchTerm.value === "") {
//     return [];
//   }

//   let matches = 0;

//   return props.products.filter((product) => {
//     if (
//       product.name.toLowerCase().includes(searchTerm.value.toLowerCase()) &&
//       matches < 10
//     ) {
//       matches++;
//       return product;
//     }
//   });
// });

// // Watch for changes in the form barcode field and update the search term
// watch(
//   () => form.barcode,
//   (newValue) => {
//     searchTerm.value = newValue;
//   }
// );

// // Method to select a product (or barcode)
// const selectProduct = (productName) => {
//   form.barcode = productName; // Set the selected product name to the barcode field
//   searchTerm.value = ""; // Clear the search term after selection
// };
</script>
