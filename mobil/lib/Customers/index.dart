import 'dart:convert';
import 'package:flutter/material.dart';
import '../../global.dart';
import 'package:http/http.dart' as http;
import 'customer_profile.dart';
import '/Layouts/main_layout.dart';
import 'package:mobil/widgets/widgets.dart';

class CustomersPage extends StatefulWidget {
  const CustomersPage({super.key});

  @override
  State<CustomersPage> createState() => _CustomersPageState();
}

class _CustomersPageState extends State<CustomersPage> {
  bool loading = true;
  List customers = [];
  List filteredCustomers = [];
  String? error;
  TextEditingController searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    fetchCustomers();
  }

  Future<void> fetchCustomers() async {
    setState(() {
      loading = true;
      error = null;
    });

    try {
      final url = Uri.parse("${apiBaseUrl}/api/customers");

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
          customers = data;
          filteredCustomers = customers; // Başlangıçta tüm listeyi göster
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

  void filterCustomers(String query) {
    final q = query.toLowerCase();
    setState(() {
      filteredCustomers = customers.where((c) {
        final fullName = "${c['first_name']} ${c['last_name']}".toLowerCase();
        return fullName.contains(q);
      }).toList();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(20),
      child: Column(
        children: [
          // Arama Kutusu
          TextField(
            controller: searchController,
            decoration: const InputDecoration(
              labelText: "Ara",
              prefixIcon: Icon(Icons.search),
              border: OutlineInputBorder(),
            ),
            onChanged: filterCustomers,
          ),
          const SizedBox(height: 20),
          Expanded(
            child: loading
                ? const Center(child: CircularProgressIndicator())
                : error != null
                ? Center(
                    child: Text(
                      error!,
                      style: const TextStyle(color: Colors.red, fontSize: 16),
                    ),
                  )
                : ListView.builder(
                    itemCount: filteredCustomers.length,
                    itemBuilder: (context, index) {
                      final c = filteredCustomers[index];
                      return AppCard(
                        title: "${c['first_name']} ${c['last_name']}",
                        subtitle:
                            "Email: ${c['email'] ?? '-'}\nPhone: ${c['phone'] ?? '-'}\nNote: ${c['note'] ?? '-'}",
                        leading: const Icon(Icons.person), // isteğe bağlı
                        trailing: const Icon(
                          Icons.arrow_forward,
                        ), // isteğe bağlı
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (context) => MainLayout(
                                child: CustomerProfilePage(customerId: c['id']),
                              ),
                            ),
                          );
                        },
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }
}
