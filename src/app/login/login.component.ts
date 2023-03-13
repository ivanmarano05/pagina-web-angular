import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { NgForm } from '@angular/forms';
import { Router } from '@angular/router';
import { AutenticacionService } from 'src/app/autenticacion.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
})
export class LoginComponent implements OnInit {
  errorLogin: boolean = false;
  autenticando: boolean = false;
  credenciales: any = {};

  constructor(private router: Router, private autenticador: AutenticacionService) { }

  ngOnInit(): void {}

  login (): void {
    this.errorLogin = false;
    this.autenticando = true;
    this.autenticador.login(this.credenciales.email, this.credenciales.clave)
      .subscribe({
        next: (response: any) => {},
        error: (err) => {
          this.errorLogin = true;
          this.autenticando = false;
        },
        complete: () => {
          this.autenticando = false;
          this.router.navigate(['/inicio']);
        }
      });
  }
}