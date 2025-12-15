import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import '../global.dart';

class EmployeListPage extends StatefulWidget {
  const EmployeListPage({super.key});

  @override
  State<EmployeListPage> createState() => _EmployeListPageState();
}

class _EmployeListPageState extends State<EmployeListPage> {
  bool loading = true;
  List employees = [];
  List filteredEmployees = [];

  final TextEditingController searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    loadEmployees();

    searchCtrl.addListener(() {
      filterEmployees(searchCtrl.text);
    });
  }

  // Çalışanları getir
  Future<void> loadEmployees() async {
    setState(() => loading = true);

    final url = Uri.parse('$apiBaseUrl/api/employees');

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
        employees = data;
        filteredEmployees = data; // ilk başta hepsi görünür
        loading = false;
      });
    } else {
      setState(() => loading = false);
    }
  }

  // 🔍 Filtreleme mantığı
  void filterEmployees(String query) {
    if (query.isEmpty) {
      setState(() {
        filteredEmployees = employees;
      });
      return;
    }

    final q = query.toLowerCase();

    setState(() {
      filteredEmployees = employees.where((emp) {
        final fullName = "${emp['first_name']} ${emp['last_name']}"
            .toLowerCase();
        return fullName.contains(q);
      }).toList();
    });
  }

  // Düzenleme popup (aynen duruyor)
  void showEditDialog(Map employee) {
    final firstNameCtrl = TextEditingController(text: employee['first_name']);
    final lastNameCtrl = TextEditingController(text: employee['last_name']);
    final emailCtrl = TextEditingController(text: employee['email']);
    final phoneCtrl = TextEditingController(text: employee['phone']);
    String role = employee['position'] ?? "Employee";

    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text("Çalışanı Güncelle"),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(
                  controller: firstNameCtrl,
                  decoration: const InputDecoration(labelText: "Ad"),
                ),
                TextField(
                  controller: lastNameCtrl,
                  decoration: const InputDecoration(labelText: "Soyad"),
                ),
                TextField(
                  controller: emailCtrl,
                  decoration: const InputDecoration(labelText: "E-posta"),
                ),
                TextField(
                  controller: phoneCtrl,
                  decoration: const InputDecoration(labelText: "Telefon"),
                ),
                DropdownButtonFormField(
                  value: role,
                  decoration: const InputDecoration(labelText: "Rol"),
                  items: const [
                    DropdownMenuItem(value: "Employee", child: Text("Çalışan")),
                    DropdownMenuItem(value: "Manager", child: Text("Müdür")),
                  ],
                  onChanged: (v) => role = v.toString(),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text("İptal"),
            ),
            ElevatedButton(
              onPressed: () {
                updateEmployee(
                  employee['id'],
                  firstNameCtrl.text,
                  lastNameCtrl.text,
                  emailCtrl.text,
                  phoneCtrl.text,
                  role,
                );
                Navigator.pop(context);
              },
              child: const Text("Kaydet"),
            ),
          ],
        );
      },
    );
  }

  // PUT isteği (aynen duruyor)
  Future<void> updateEmployee(
    int id,
    String firstName,
    String lastName,
    String email,
    String phone,
    String role,
  ) async {
    final url = Uri.parse('$apiBaseUrl/api/employees/$id');

    final response = await http.put(
      url,
      headers: {
        "Accept": "application/json",
        "Content-Type": "application/json",
        "Authorization": "Bearer $globalToken",
      },
      body: jsonEncode({
        "first_name": firstName,
        "last_name": lastName,
        "email": email,
        "phone": phone,
        "position": role,
      }),
    );

    if (response.statusCode == 200) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text("✅ Güncelleme başarılı")));
      loadEmployees();
    } else {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text("❌ Hata: ${response.body}")));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("Çalışan Listesi")),
      body: loading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                // 🔍 SEARCH BOX
                Padding(
                  padding: const EdgeInsets.all(10),
                  child: TextField(
                    controller: searchCtrl,
                    decoration: InputDecoration(
                      hintText: "Çalışan ara ",
                      prefixIcon: const Icon(Icons.search),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                  ),
                ),

                // LİSTE
                Expanded(
                  child: RefreshIndicator(
                    onRefresh: loadEmployees,
                    child: ListView.builder(
                      itemCount: filteredEmployees.length,
                      itemBuilder: (context, index) {
                        final emp = filteredEmployees[index];
                        return Card(
                          child: ListTile(
                            title: Text(
                              "${emp['first_name']} ${emp['last_name']}",
                            ),
                            subtitle: Text(
                              "${emp['email']} • ${emp['position'] ?? ''}",
                            ),
                            trailing: IconButton(
                              icon: const Icon(Icons.edit),
                              onPressed: () => showEditDialog(emp),
                            ),
                          ),
                        );
                      },
                    ),
                  ),
                ),
              ],
            ),
    );
  }
}
