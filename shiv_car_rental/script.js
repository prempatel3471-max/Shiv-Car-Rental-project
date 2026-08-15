/* =================================
   SHIV CAR RENTAL
   MAIN JAVASCRIPT
================================= */


/* ================================
   MOBILE MENU
================================ */

const menuToggle =
    document.getElementById("menuToggle");

const navMenu =
    document.getElementById("navMenu");

menuToggle.addEventListener("click", function () {

    navMenu.classList.toggle("open");

    const icon =
        menuToggle.querySelector("i");

    if (navMenu.classList.contains("open")) {

        icon.classList.remove("fa-bars");

        icon.classList.add("fa-xmark");

    } else {

        icon.classList.remove("fa-xmark");

        icon.classList.add("fa-bars");

    }

});


/* Close mobile menu */

document.querySelectorAll(".nav-link").forEach(function (link) {

    link.addEventListener("click", function () {

        navMenu.classList.remove("open");

        const icon =
            menuToggle.querySelector("i");

        icon.classList.remove("fa-xmark");

        icon.classList.add("fa-bars");

    });

});


/* ================================
   ACTIVE NAVIGATION
================================ */

const sections =
    document.querySelectorAll("section[id]");

const navLinks =
    document.querySelectorAll(".nav-link");

window.addEventListener("scroll", function () {

    let current = "";

    sections.forEach(function (section) {

        const sectionTop =
            section.offsetTop - 150;

        if (window.scrollY >= sectionTop) {

            current =
                section.getAttribute("id");

        }

    });

    navLinks.forEach(function (link) {

        link.classList.remove("active");

        if (
            link.getAttribute("href") ===
            "#" + current
        ) {

            link.classList.add("active");

        }

    });

});


/* ================================
   SEARCH CARS
================================ */

function searchCars() {

    const location =
        document
            .getElementById("locationInput")
            .value
            .trim();

    const pickup =
        document
            .getElementById("pickupDate")
            .value;

    const returnDate =
        document
            .getElementById("returnDate")
            .value;


    if (!location) {

        showToast(
            "Please enter a location."
        );

        return;

    }


    if (!pickup || !returnDate) {

        showToast(
            "Please select pickup and return dates."
        );

        return;

    }


    if (returnDate < pickup) {

        showToast(
            "Return date cannot be before pickup date."
        );

        return;

    }


    document
        .getElementById("cars")
        .scrollIntoView({
            behavior: "smooth"
        });


    showToast(
        "Cars available in " + location
    );

}


/* ================================
   FILTER CARS
================================ */

function filterCars() {

    const search = document
        .getElementById("carSearch")
        .value
        .toLowerCase()
        .trim();

    const cars = document.querySelectorAll(".car-card");
    let found = false;

    cars.forEach(function (car) {
        const name = (car.dataset.name || "").toLowerCase();
        const category = car.dataset.category || "all";
        const matchesSearch = name.includes(search);
        const matchesCategory = activeCategory === "all" || category === activeCategory;

        if (matchesSearch && matchesCategory) {
            car.style.display = "";
            found = true;
        } else {
            car.style.display = "none";
        }
    });

    if (!found && search !== "") {
        showToast("No matching car found.");
    }
}


/* ================================
   SORT CARS
================================ */

function sortCars() {

    const sort =
        document
            .getElementById("priceSort")
            .value;

    const grid =
        document.getElementById("fleetGrid");

    const cars =
        Array.from(
            grid.querySelectorAll(".car-card")
        );


    if (sort === "low") {

        cars.sort(function (a, b) {

            return (
                Number(a.dataset.price) -
                Number(b.dataset.price)
            );

        });

    }


    if (sort === "high") {

        cars.sort(function (a, b) {

            return (
                Number(b.dataset.price) -
                Number(a.dataset.price)
            );

        });

    }


    cars.forEach(function (car) {

        grid.appendChild(car);

    });


    if (sort !== "default") {

        showToast("Cars sorted by price.");

    }

}


/* ================================
   FAVORITE
================================ */

function toggleFavorite(button) {

    button.classList.toggle("active");

    const icon =
        button.querySelector("i");


    if (button.classList.contains("active")) {

        icon.classList.remove("far");

        icon.classList.add("fas");

        showToast(
            "Car added to favorites ❤️"
        );

    } else {

        icon.classList.remove("fas");

        icon.classList.add("far");

        showToast(
            "Car removed from favorites."
        );

    }

}


/* ================================
   CATEGORY FILTER
================================ */

let activeCategory = "all";

function filterCategory(category, button) {
    activeCategory = category;

    document.querySelectorAll(".category-btn").forEach(function (btn) {
        btn.classList.remove("active");
    });

    if (button) button.classList.add("active");

    const searchInput = document.getElementById("carSearch");
    const search = searchInput ? searchInput.value.toLowerCase().trim() : "";
    const cars = document.querySelectorAll(".car-card");
    let found = false;

    cars.forEach(function (car) {
        const name = (car.dataset.name || "").toLowerCase();
        const carCategory = car.dataset.category || "all";
        const matchesCategory = category === "all" || carCategory === category;
        const matchesSearch = !search || name.includes(search);
        const visible = matchesCategory && matchesSearch;

        car.style.display = visible ? "" : "none";
        if (visible) found = true;
    });

    if (!found) showToast("No cars found in this category.");
}

/* ================================
   CAR DETAILS
================================ */

function showDetails(carName, price) {

    const modal =
        document.getElementById("modal");

    const body =
        document.getElementById("modalBody");


    body.innerHTML = `

        <div class="modal-icon"
             style="
                width:70px;
                height:70px;
                border-radius:50%;
                background:#fff0ec;
                color:#ff4d30;
                display:flex;
                align-items:center;
                justify-content:center;
                font-size:28px;
                margin-bottom:15px;
             ">

            <i class="fas fa-car"></i>

        </div>

        <h2>${carName}</h2>

        <p>
            <strong>Rental Price:</strong>
            ₹${price} / hour
        </p>

        <p>
            <strong>Transmission:</strong>
            Automatic / Manual
        </p>

        <p>
            <strong>Fuel:</strong>
            Petrol / Diesel
        </p>

        <p>
            <strong>Seats:</strong>
            5 Seats
        </p>

        <p>
            <strong>Air Conditioning:</strong>
            Available
        </p>

        <br>

        <button
            class="modal-submit"
            onclick="openBooking('${carName}',${price})">

            Book This Car

        </button>

    `;


    modal.classList.add("active");

}


/* ================================
   BOOKING
================================ */

function openBooking(carName, price) {
    const modal = document.getElementById("modal");
    const body = document.getElementById("modalBody");
    const now = new Date();
    const today = now.toISOString().split("T")[0];
    const currentTime = String(now.getHours()).padStart(2,"0") + ":" + String(now.getMinutes()).padStart(2,"0");

    body.innerHTML = `
        <div class="booking-heading">
            <span class="booking-icon"><i class="fas fa-car"></i></span>
            <div>
                <h2>Book ${carName}</h2>
                <p>Starting from <strong>₹${price}/hour</strong></p>
            </div>
        </div>

        <form class="modal-form booking-form" onsubmit="confirmBooking(event,'${carName}',${price})">
            <div class="form-row">
                <input type="text" id="customerName" placeholder="Your Name" required>
                <input type="tel" id="customerPhone" placeholder="Phone Number" pattern="[0-9]{10}" title="Enter a valid 10-digit phone number" required>
            </div>
            <div class="form-row">
                <input type="text" id="pickupLocation" placeholder="Pickup City" required>
                <select id="driverOption" required>
                    <option value="">Driver option</option>
                    <option value="self">Self Drive</option>
                    <option value="driver">With Driver</option>
                </select>
            </div>
            <div class="form-row">
                <label class="date-field"><span>Pickup Date</span><input type="date" id="bookingPickup" min="${today}" required></label>
                <label class="date-field"><span>Pickup Time</span><input type="time" id="bookingTime" value="${currentTime}" required></label>
            </div>
            <div class="form-row">
                <label class="date-field" style="flex:1"><span>Rental Hours</span>
                    <select id="bookingHours" required>
                        <option value="1">1 hour</option>
                        <option value="2">2 hours</option>
                        <option value="3">3 hours</option>
                        <option value="4">4 hours</option>
                        <option value="5">5 hours</option>
                        <option value="6">6 hours</option>
                        <option value="8">8 hours</option>
                        <option value="10">10 hours</option>
                        <option value="12">12 hours</option>
                        <option value="24">24 hours</option>
                        <option value="48">48 hours</option>
                        <option value="72">72 hours</option>
                    </select>
                </label>
            </div>

            <div class="booking-summary">
                <div><span>Car</span><strong>${carName}</strong></div>
                <div><span>Rate</span><strong>₹${price}/hour</strong></div>
                <div><span>Rental duration</span><strong id="estimatedHours">1 hour</strong></div>
                <div class="summary-total"><span>Estimated Total</span><strong id="bookingTotal">₹${price}</strong></div>
            </div>
            <p class="payment-note"><i class="fas fa-info-circle"></i> The selected car is reserved for your requested time after the booking is created. Already-booked time slots will be rejected.</p>

            <button type="submit" class="modal-submit">
                <i class="fas fa-check"></i> Continue to Cash Booking
            </button>
        </form>
    `;

    const pickup = document.getElementById("bookingPickup");
    const time = document.getElementById("bookingTime");
    const hoursInput = document.getElementById("bookingHours");

    function updateTotal() {
        const hours = Number(hoursInput.value || 1);
        document.getElementById("estimatedHours").textContent = hours + (hours === 1 ? " hour" : " hours");
        document.getElementById("bookingTotal").textContent = "₹" + (hours * price).toFixed(2);
    }
    hoursInput.addEventListener("change", updateTotal);
    updateTotal();
    modal.classList.add("active");
}

/* ================================
   CONFIRM BOOKING
================================ */

function confirmBooking(event, carName, price) {
    event.preventDefault();
    const name = document.getElementById("customerName").value.trim();
    const phone = document.getElementById("customerPhone").value.trim();
    const pickup = document.getElementById("bookingPickup").value;
    const pickupTime = document.getElementById("bookingTime").value;
    const hours = Number(document.getElementById("bookingHours").value);
    const location = document.getElementById("pickupLocation").value.trim();
    const driver = document.getElementById("driverOption").value;

    if (!name || !phone || !pickup || !pickupTime || !hours || !location || !driver) {
        showToast("Please complete all booking details.");
        return;
    }
    showToast("Checking car availability...");
    fetch("backend/api/bookings.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            car_name: carName,
            customer_name: name,
            customer_phone: phone,
            pickup_location: location,
            pickup_date: pickup,
            pickup_time: pickupTime,
            hours: hours,
            driver_option: driver
        })
    })
    .then(async response => {
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || "Could not create booking.");
        return data;
    })
    .then(data => { closeModal(); openPaymentPage(data.booking); })
    .catch(error => { showToast(error.message || "Booking failed. Please try again."); console.error(error); });
}

/* ================================
   PAYMENT PAGE
================================ */

function openPaymentPage(booking) {
    const modal = document.getElementById("modal");
    const body = document.getElementById("modalBody");
    const total = Number(booking.total_amount || 0);

    body.innerHTML = `
        <div class="payment-page">
            <div class="payment-header">
                <span class="booking-icon"><i class="fas fa-money-bill-wave"></i></span>
                <div>
                    <h2>Cash Payment</h2>
                    <p>Booking <strong>${booking.code}</strong> will be confirmed for cash payment.</p>
                </div>
            </div>

            <div class="payment-summary">
                <div><span>Car</span><strong>${booking.car}</strong></div>
                <div><span>Rental hours</span><strong>${booking.hours}</strong></div>
                <div><span>Rental amount</span><strong>₹${Number(booking.base_amount).toFixed(2)}</strong></div>
                <div><span>Driver fee</span><strong>₹${Number(booking.driver_fee).toFixed(2)}</strong></div>
                <div><span>Tax (18%)</span><strong>₹${Number(booking.tax_amount).toFixed(2)}</strong></div>
                <div class="summary-total"><span>Total payable in cash</span><strong>₹${total.toFixed(2)}</strong></div>
            </div>

            <div class="cash-payment-box">
                <i class="fas fa-money-bill-wave"></i>
                <div>
                    <strong>Pay by Cash</strong>
                    <p>Pay the full amount in cash when you collect the car. No online payment is required.</p>
                </div>
            </div>

            <button class="modal-submit" onclick="completePayment(${booking.id})">
                <i class="fas fa-check"></i> Confirm Cash Booking
            </button>
            <p class="payment-note"><i class="fas fa-info-circle"></i> Payment method: Cash at pickup</p>
        </div>
    `;

    modal.classList.add("active");
}

function completePayment(bookingId) {
    showToast("Confirming cash booking...");

    fetch("backend/api/payment.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            booking_id: bookingId,
            payment_method: "cash"
        })
    })
    .then(async response => {
        const data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.message || "Could not confirm cash booking.");
        }
        return data;
    })
    .then(data => {
        showPaymentSuccess(data.payment);
    })
    .catch(error => {
        showToast(error.message || "Could not confirm cash booking.");
        console.error(error);
    });
}

function showPaymentSuccess(payment) {
    const body = document.getElementById("modalBody");
    body.innerHTML = `
        <div class="payment-success">
            <div class="success-icon"><i class="fas fa-check"></i></div>
            <h2>Booking Confirmed!</h2>
            <p>Your cash payment will be collected at pickup.</p>
            <div class="payment-success-card">
                <div><span>Booking ID</span><strong>${payment.booking_code}</strong></div>
                <div><span>Payment Method</span><strong>CASH AT PICKUP</strong></div>
                <div><span>Amount Paid</span><strong>₹${Number(payment.amount).toFixed(2)}</strong></div>
                <div><span>Status</span><strong class="success-text">CONFIRMED / CASH DUE</strong></div>
            </div>
            <button class="modal-submit" onclick="closeModal(); showToast('Booking confirmed successfully!')">
                <i class="fas fa-check"></i> Done
            </button>
        </div>
    `;
}



/* ================================
   CITY
================================ */

function selectCity(city) {

    document
        .getElementById("locationInput")
        .value = city;

    document
        .getElementById("home")
        .scrollIntoView({
            behavior: "smooth"
        });

    showToast(
        "Location selected: " + city
    );

}


/* ================================
   CONTACT
================================ */

function contactUs() {

    const modal =
        document.getElementById("modal");

    const body =
        document.getElementById("modalBody");


    body.innerHTML = `

        <h2>
            Contact Shiv Car Rental
        </h2>

        <p>
            <i class="fas fa-phone"></i>
            +91 98765 43210
        </p>

        <p>
            <i class="fas fa-envelope"></i>
            support@shivcarrental.com
        </p>

        <p>
            <i class="fas fa-clock"></i>
            Available 24/7
        </p>

        <br>

        <button
            class="modal-submit"
            onclick="closeModal()">

            Close

        </button>

    `;


    modal.classList.add("active");

}


/* ================================
   CLOSE MODAL
================================ */

function closeModal() {

    document
        .getElementById("modal")
        .classList.remove("active");

}


/* Close modal when clicking outside */

document
    .getElementById("modal")
    .addEventListener("click", function(event) {

        if (
            event.target === this
        ) {

            closeModal();

        }

    });


/* ESC key */

document.addEventListener("keydown", function(event) {

    if (event.key === "Escape") {

        closeModal();

    }

});


/* ================================
   TOAST
================================ */

let toastTimer;

function showToast(message) {

    const toast =
        document.getElementById("toast");

    const messageElement =
        document.getElementById("toastMessage");


    messageElement.textContent =
        message;


    toast.classList.add("show");


    clearTimeout(toastTimer);


    toastTimer =
        setTimeout(function() {

            toast.classList.remove("show");

        }, 3000);

}