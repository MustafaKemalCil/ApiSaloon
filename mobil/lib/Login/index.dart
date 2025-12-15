import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:mobil/Dashboard/index.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../Home/index.dart';
import '../global.dart';
import '/Layouts/main_layout.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final emailController = TextEditingController();
  final passwordController = TextEditingController();

  bool loading = false;
  Future<void> saveToken(String token) async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
  }

  Future<void> login() async {
    setState(() => loading = true);

    try {
      final url = Uri.parse("$apiBaseUrl/api/login");

      final response = await http.post(
        url,
        headers: {
          "Accept": "application/json",
          "Content-Type": "application/json",
        },
        body: jsonEncode({
          "email": emailController.text.trim(),
          "password": passwordController.text,
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);

        if (data["token"] == null) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text("API token göndermedi!")),
          );
          setState(() => loading = false);
          return;
        }

        // GLOBAL token
        globalToken = data["token"];
        final token = data["token"];
        await saveToken(token);
        // LOCAL kaydet
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString("token", data["token"]);

        if (!mounted) return;

        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
            builder: (_) => const MainLayout(child: DashboardPage()),
          ),
        );
      } else {
        // ❗ HATALI GİRİŞTE BİLDİRİM BURADA
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text("Giriş başarısız: ${response.body}"),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      // ❗ BAĞLANTI HATASI
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text("Bağlantı hatası: $e"),
          backgroundColor: Colors.red,
        ),
      );
    }

    setState(() => loading = false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("Giriş Yap")),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            TextField(
              controller: emailController,
              decoration: const InputDecoration(
                labelText: "Email",
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 15),

            TextField(
              controller: passwordController,
              obscureText: true,
              decoration: const InputDecoration(
                labelText: "Şifre",
                border: OutlineInputBorder(),
              ),
            ),

            const SizedBox(height: 20),

            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton(
                onPressed: login, // 🔥 HER ZAMAN AKTİF
                child: loading
                    ? const CircularProgressIndicator(color: Colors.white)
                    : const Text("Giriş Yap"),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
