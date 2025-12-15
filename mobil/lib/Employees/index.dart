import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import '../global.dart';

class EmployeePage extends StatefulWidget {
  const EmployeePage({super.key});

  @override
  State<EmployeePage> createState() => _EmployeePageState();
}

class _EmployeePageState extends State<EmployeePage> {
  final _formKey = GlobalKey<FormState>();

  final TextEditingController firstNameCtrl = TextEditingController();
  final TextEditingController lastNameCtrl = TextEditingController();
  final TextEditingController emailCtrl = TextEditingController();
  final TextEditingController phoneCtrl = TextEditingController();
  final TextEditingController passwordCtrl = TextEditingController();
  final TextEditingController confirmCtrl = TextEditingController();

  String role = "Employee";
  bool loading = false;

  Future<void> submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => loading = true);

    final url = Uri.parse('$apiBaseUrl/api/employees');

    try {
      final response = await http.post(
        url,
        headers: {
          "Accept": "application/json",
          "Content-Type": "application/json",
          "Authorization": "Bearer $globalToken",
        },
        body: jsonEncode({
          "first_name": firstNameCtrl.text,
          "last_name": lastNameCtrl.text,
          "email": emailCtrl.text,
          "phone": phoneCtrl.text,
          "position": role, // Employee / Manager
          "password": passwordCtrl.text,
          "password_confirmation": confirmCtrl.text,
        }),
      );

      setState(() => loading = false);

      if (response.statusCode == 201 || response.statusCode == 200) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text("✅ Çalışan başarıyla eklendi")),
        );

        // Formu temizle
        firstNameCtrl.clear();
        lastNameCtrl.clear();
        emailCtrl.clear();
        phoneCtrl.clear();
        passwordCtrl.clear();
        confirmCtrl.clear();

        setState(() => role = "Employee");
      } else {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text("❌ Hata: ${response.body}")));
      }
    } catch (e) {
      setState(() => loading = false);
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text("❌ Bağlantı hatası: $e")));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("Çalışan Ekle")),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: ListView(
            children: [
              TextFormField(
                controller: firstNameCtrl,
                decoration: const InputDecoration(labelText: "Ad"),
                validator: (v) => v!.isEmpty ? "Ad zorunlu" : null,
              ),
              TextFormField(
                controller: lastNameCtrl,
                decoration: const InputDecoration(labelText: "Soyad"),
                validator: (v) => v!.isEmpty ? "Soyad zorunlu" : null,
              ),
              TextFormField(
                controller: emailCtrl,
                decoration: const InputDecoration(labelText: "E-posta"),
                validator: (v) => v!.isEmpty ? "E-posta zorunlu" : null,
              ),
              TextFormField(
                controller: phoneCtrl,
                decoration: const InputDecoration(labelText: "Telefon"),
              ),
              const SizedBox(height: 8),

              DropdownButtonFormField(
                value: role,
                decoration: const InputDecoration(labelText: "Rol"),
                items: const [
                  DropdownMenuItem(value: "Employee", child: Text("Çalışan")),
                  DropdownMenuItem(value: "Manager", child: Text("Müdür")),
                ],
                onChanged: (v) => setState(() => role = v.toString()),
              ),

              const SizedBox(height: 8),
              TextFormField(
                controller: passwordCtrl,
                decoration: const InputDecoration(labelText: "Şifre"),
                obscureText: true,
                validator: (v) => v!.length < 6 ? "En az 6 karakter" : null,
              ),
              TextFormField(
                controller: confirmCtrl,
                decoration: const InputDecoration(labelText: "Şifre Tekrar"),
                obscureText: true,
                validator: (v) =>
                    v != passwordCtrl.text ? "Şifreler uyuşmuyor" : null,
              ),

              const SizedBox(height: 20),
              SizedBox(
                height: 50,
                child: ElevatedButton(
                  onPressed: loading ? null : submit,
                  child: loading
                      ? const CircularProgressIndicator(color: Colors.white)
                      : const Text("Kaydet"),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
