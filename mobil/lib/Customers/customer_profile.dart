import 'dart:convert';
import 'package:flutter/material.dart';
import '../../global.dart'; // apiBaseUrl ve globalToken burada olmalı
import 'package:http/http.dart' as http;
import 'package:intl/intl.dart';
import 'package:mobil/widgets/widgets.dart';

class CustomerProfilePage extends StatefulWidget {
  final int customerId;

  const CustomerProfilePage({super.key, required this.customerId});

  @override
  State<CustomerProfilePage> createState() => _CustomerProfilePageState();
}

class _CustomerProfilePageState extends State<CustomerProfilePage> {
  double getTotalPayment(List<dynamic> paymentsList) {
    return paymentsList.fold<double>(0.0, (sum, payment) {
      final raw = payment['amount'];

      double amount = 0.0;

      if (raw is int) {
        amount = raw.toDouble();
      } else if (raw is double) {
        amount = raw;
      } else if (raw is String) {
        amount = double.tryParse(raw) ?? 0.0;
      }

      return sum + amount;
    });
  }

  Map<String, dynamic>? customer;
  List<dynamic> appointments = [];
  bool loading = true;
  String? error;

  // Bu değişken butona basıldığında API'den çekilen paymentleri tutacak
  List<dynamic> paymentHistory = [];
  String selectedStatus =
      'completed'; // sayfa açıldığında completed listelensin
  List<dynamic> filteredAppointments = [];

  @override
  void initState() {
    super.initState();
    fetchCustomer();
  }

  Widget filterButton(String label, String statusValue) {
    final isSelected = selectedStatus == statusValue;
    return ElevatedButton(
      style: ElevatedButton.styleFrom(
        backgroundColor: isSelected ? Colors.blue : Colors.grey,
        padding: const EdgeInsets.symmetric(
          horizontal: 8,
          vertical: 4,
        ), // buton iç boşluğu
        minimumSize: const Size(60, 30), // minimum genişlik ve yükseklik
        textStyle: const TextStyle(fontSize: 12), // yazı boyutu
      ),
      onPressed: () {
        setState(() {
          selectedStatus = statusValue;
          filteredAppointments = appointments
              .where((appt) => appt['status'] == selectedStatus)
              .toList();
        });
      },
      child: Text(label),
    );
  }

  Future<void> fetchCustomer() async {
    setState(() {
      loading = true;
      error = null;
    });

    try {
      final url = Uri.parse("${apiBaseUrl}/api/customers/${widget.customerId}");
      final response = await http.get(
        url,
        headers: {
          "Accept": "application/json",
          "Authorization": "Bearer $globalToken",
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        setState(() {
          customer = data['customer'];
          appointments = data['appointments'] ?? [];
          filteredAppointments = appointments
              .where((appt) => appt['status'] == selectedStatus)
              .toList();
          loading = false;
        });
      } else if (response.statusCode == 401) {
        setState(() {
          error = "Yetkisiz erişim! Token geçersiz veya yok.";
          loading = false;
        });
      } else {
        setState(() {
          error = "Hata oluştu: ${response.statusCode}";
          loading = false;
        });
      }
    } catch (e) {
      setState(() {
        error = "Bağlantı hatası: $e";
        loading = false;
      });
    }
  }

  // API'den ödeme geçmişini çeken fonksiyon
  Future<void> addPayment(
    int appointmentId,
    String amount,
    String? note,
  ) async {
    try {
      final url = Uri.parse(
        "$apiBaseUrl/api/appointments/$appointmentId/payments",
      );
      final response = await http.post(
        url,
        headers: {
          "Accept": "application/json",
          "Authorization": "Bearer $globalToken",
          "Content-Type": "application/json",
        },
        body: jsonEncode({
          "amount": amount,
          "note": note,
          "customer_id": customer!['id'],
        }),
      );

      if (response.statusCode == 200) {
        // --- sadece bu satır yeterli ---
        await fetchCustomer(); // fetchCustomer içinde zaten setState var

        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text("Payment added successfully.")),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text("Failed to add payment: ${response.statusCode}"),
          ),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text("Error adding payment: $e")));
    }
  }

  Future<void> fetchPaymentHistory(int appointmentId) async {
    try {
      final url = Uri.parse(
        "$apiBaseUrl/api/appointments/$appointmentId/payments",
      );
      final response = await http.get(
        url,
        headers: {
          "Accept": "application/json",
          "Authorization": "Bearer $globalToken",
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        setState(() {
          paymentHistory = data['payments'] ?? [];
        });
      } else {
        setState(() {
          paymentHistory = [];
        });
      }
    } catch (e) {
      setState(() {
        paymentHistory = [];
      });
    }
  }

  Future<void> updateAppointmentStatus(int appointmentId, String status) async {
    try {
      final url = Uri.parse("$apiBaseUrl/api/appointments/$appointmentId");

      final response = await http.put(
        url,
        headers: {
          "Accept": "application/json",
          "Authorization": "Bearer $globalToken",
          "Content-Type": "application/json",
        },
        body: jsonEncode({"status": status}),
      );

      if (response.statusCode == 200) {
        await fetchCustomer(); // listeyi yenile
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text("Durum güncellendi: $status")));
      } else {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(const SnackBar(content: Text("Durum güncellenemedi")));
      }
    } catch (e) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text("Hata: $e")));
    }
  }

  @override
  Widget build(BuildContext context) {
    // İptal edilmeyen randevular
    final validAppointments = appointments
        .where((a) => a['status'] != 'canceled')
        .toList();

    // Toplamlar
    int total = appointments.length;

    int canceledCount = appointments
        .where((a) => a['status'] == 'canceled')
        .length;

    int completedCount = appointments
        .where((a) => a['status'] == 'completed')
        .length;

    int paidCount = appointments.where((a) => a['status'] == 'paid').length;
    return Scaffold(
      body: loading
          ? const Center(child: CircularProgressIndicator())
          : error != null
          ? Center(
              child: Text(error!, style: const TextStyle(color: Colors.red)),
            )
          : SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Müşteri Bilgileri
                  Text(
                    "${customer!['first_name']} ${customer!['last_name']}",
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text("${customer!['email'] ?? '-'}"),
                  Text("Phone: ${customer!['phone'] ?? '-'}"),
                  Text("Açıklama: ${customer!['note'] ?? '-'}"),
                  Text("Toplam Randevu: $total"),
                  Text("Toplam Randevu İptali: $canceledCount"),
                  Text("Toplam Tamamlanmış Randevu: $completedCount"),
                  Text("Toplam Ödemesi Bitmiş Randevu: $paidCount"),
                  const SizedBox(height: 20),
                  // Toplam hizmet bedeli
                  Text(
                    "Toplam Hizmet: ${validAppointments.fold<double>(0.0, (sum, appt) => sum + (double.tryParse(appt['cost'].toString()) ?? 0))}",
                  ),

                  // Toplam ödeme
                  Text(
                    "Toplam Ödeme: ${validAppointments.fold<double>(0.0, (sum, appt) => sum + getTotalPayment(appt['payments'] ?? []))}",
                  ),
                  Row(
                    children: [
                      filterButton('Pending', 'pending'),
                      const SizedBox(width: 4),
                      filterButton('Completed', 'completed'),
                      const SizedBox(width: 4),
                      filterButton('Canceled', 'canceled'),
                      const SizedBox(width: 4),
                      filterButton('Paid', 'paid'),
                    ],
                  ),

                  const SizedBox(height: 20),

                  const SizedBox(height: 10),
                  filteredAppointments.isEmpty
                      ? const Text("No past appointments.")
                      : ListView.builder(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          itemCount: filteredAppointments.length,
                          itemBuilder: (context, index) {
                            final appt = filteredAppointments[index];

                            final paymentsList = appt['payments'] ?? [];
                            final totalPayment = getTotalPayment(paymentsList);

                            final start = DateTime.parse(
                              appt['start_datetime'],
                            );
                            final end = DateTime.parse(appt['end_datetime']);
                            final formatterDate = DateFormat('dd.MM.yyyy');
                            final formatterTime = DateFormat('HH:mm');
                            final dateStr = formatterDate.format(start);
                            final timeStr =
                                "${formatterTime.format(start)}-${formatterTime.format(end)}";

                            return AppCard(
                              type: AppCardType.normal,
                              child: Padding(
                                padding: const EdgeInsets.all(16),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    // 1. Satır: Service solda, Cost sağda
                                    Row(
                                      mainAxisAlignment:
                                          MainAxisAlignment.spaceBetween,
                                      children: [
                                        Text(
                                          "${appt['service']}  ",
                                          style: const TextStyle(
                                            fontSize: 18,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                        Text("$dateStr $timeStr"),
                                      ],
                                    ),
                                    const SizedBox(height: 8),

                                    // 2. Satır: Date solda, Total Payment sağda
                                    Row(
                                      mainAxisAlignment:
                                          MainAxisAlignment.spaceBetween,
                                      children: [
                                        Text(
                                          "Ücret: ${appt['cost'] ?? '-'}",
                                          style: const TextStyle(fontSize: 16),
                                        ),
                                        Text("Ödeme: ₺$totalPayment"),
                                      ],
                                    ),
                                    const SizedBox(height: 4),

                                    // 3. Satır: Note solda, Worker sağda
                                    Row(
                                      mainAxisAlignment:
                                          MainAxisAlignment.spaceBetween,
                                      children: [
                                        Text(
                                          "${customer!['first_name']} ${customer!['last_name']}",
                                        ),
                                        Text(" ${appt['worker_name'] ?? '-'}"),
                                      ],
                                    ),
                                    Padding(
                                      padding: const EdgeInsets.only(
                                        top: 8,
                                      ), // üst boşluk
                                      child: Row(
                                        mainAxisAlignment:
                                            MainAxisAlignment.start,
                                        children: [
                                          Expanded(
                                            child: Text(
                                              "${appt['note'] ?? '-'}",
                                              softWrap: true,
                                              style: const TextStyle(
                                                fontSize: 16,
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),

                                    const SizedBox(height: 10),
                                    Row(
                                      mainAxisAlignment:
                                          MainAxisAlignment.spaceBetween,
                                      children: [
                                        // Ayrıntı Butonu
                                        ElevatedButton(
                                          onPressed: () async {
                                            await fetchPaymentHistory(
                                              appt['id'],
                                            );
                                            showDialog(
                                              context: context,
                                              builder: (context) => AlertDialog(
                                                title: Text(
                                                  "Payments for Appointment ID: ${appt['id']}",
                                                ),
                                                content: paymentHistory.isEmpty
                                                    ? const Text(
                                                        "No payments available.",
                                                      )
                                                    : Column(
                                                        mainAxisSize:
                                                            MainAxisSize.min,
                                                        children: paymentHistory.map<Widget>((
                                                          payment,
                                                        ) {
                                                          final paymentDate =
                                                              DateTime.parse(
                                                                payment['created_at'],
                                                              );
                                                          final formattedDate =
                                                              DateFormat(
                                                                'dd.MM.yyyy HH:mm',
                                                              ).format(
                                                                paymentDate,
                                                              );
                                                          return Padding(
                                                            padding:
                                                                const EdgeInsets.symmetric(
                                                                  vertical: 4,
                                                                ),
                                                            child: Column(
                                                              crossAxisAlignment:
                                                                  CrossAxisAlignment
                                                                      .start,
                                                              children: [
                                                                Text(
                                                                  "Payment Date: $formattedDate",
                                                                ),
                                                                Text(
                                                                  "Amount: ${payment['amount']}",
                                                                ),
                                                                Text(
                                                                  "Method: ${payment['method'] ?? '-'}",
                                                                ),
                                                                Text(
                                                                  "Note: ${payment['note'] ?? '-'}",
                                                                ),
                                                              ],
                                                            ),
                                                          );
                                                        }).toList(),
                                                      ),
                                                actions: [
                                                  TextButton(
                                                    onPressed: () =>
                                                        Navigator.pop(context),
                                                    child: const Text("Close"),
                                                  ),
                                                ],
                                              ),
                                            );
                                          },
                                          child: const Text("Ayrıntı"),
                                        ),
                                        const SizedBox(height: 5),
                                        // ✅ DURUM BUTONU (YENİ EKLENDİ)
                                        ElevatedButton(
                                          onPressed: () {
                                            String tempStatus = appt['status'];

                                            showDialog(
                                              context: context,
                                              builder: (context) => AlertDialog(
                                                title: const Text(
                                                  "Durum Güncelle",
                                                ),
                                                content: StatefulBuilder(
                                                  builder: (context, setState) {
                                                    return DropdownButton<
                                                      String
                                                    >(
                                                      value: tempStatus,
                                                      items: const [
                                                        DropdownMenuItem(
                                                          value: "pending",
                                                          child: Text(
                                                            "Pending",
                                                          ),
                                                        ),
                                                        DropdownMenuItem(
                                                          value: "completed",
                                                          child: Text(
                                                            "Completed",
                                                          ),
                                                        ),
                                                        DropdownMenuItem(
                                                          value: "canceled",
                                                          child: Text(
                                                            "Canceled",
                                                          ),
                                                        ),
                                                        DropdownMenuItem(
                                                          value: "paid",
                                                          child: Text("Paid"),
                                                        ),
                                                      ],
                                                      onChanged: (value) {
                                                        setState(() {
                                                          tempStatus = value!;
                                                        });
                                                      },
                                                    );
                                                  },
                                                ),
                                                actions: [
                                                  TextButton(
                                                    onPressed: () =>
                                                        Navigator.pop(context),
                                                    child: const Text("İptal"),
                                                  ),
                                                  TextButton(
                                                    onPressed: () async {
                                                      Navigator.pop(context);
                                                      await updateAppointmentStatus(
                                                        appt['id'],
                                                        tempStatus,
                                                      );
                                                    },
                                                    child: const Text("Kaydet"),
                                                  ),
                                                ],
                                              ),
                                            );
                                          },
                                          child: const Text("Durum"),
                                        ),
                                        // Ödeme Ekle Butonu
                                        ElevatedButton(
                                          onPressed: () {
                                            String amount = '';
                                            String? note;

                                            showDialog(
                                              context: context,
                                              builder: (context) => AlertDialog(
                                                title: const Text(
                                                  "Add Payment",
                                                ),
                                                content: Column(
                                                  mainAxisSize:
                                                      MainAxisSize.min,
                                                  children: [
                                                    TextField(
                                                      keyboardType:
                                                          TextInputType.number,
                                                      decoration:
                                                          const InputDecoration(
                                                            labelText: "Amount",
                                                          ),
                                                      onChanged: (val) =>
                                                          amount = val,
                                                    ),
                                                    TextField(
                                                      decoration:
                                                          const InputDecoration(
                                                            labelText: "Note",
                                                          ),
                                                      onChanged: (val) =>
                                                          note = val,
                                                    ),
                                                  ],
                                                ),
                                                actions: [
                                                  TextButton(
                                                    onPressed: () =>
                                                        Navigator.pop(context),
                                                    child: const Text("Cancel"),
                                                  ),
                                                  TextButton(
                                                    onPressed: () async {
                                                      Navigator.pop(context);
                                                      await addPayment(
                                                        appt['id'],
                                                        amount,
                                                        note,
                                                      );
                                                    },
                                                    child: const Text("Add"),
                                                  ),
                                                ],
                                              ),
                                            );
                                          },
                                          child: const Text("Ödeme Ekle"),
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                            );
                          },
                        ),
                ],
              ),
            ),
    );
  }
}
