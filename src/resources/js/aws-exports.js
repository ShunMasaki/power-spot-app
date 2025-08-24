const awsconfig = {
    Auth: {
      Cognito: {
        userPoolId: 'us-east-1_fbJdT2Z0S',
        userPoolClientId: '2lev0bbr4un1so7aipuphl0o9o',
        loginWith: {
          oauth: {
            domain: 'us-east-1fbjdt2z0s.auth.us-east-1.amazoncognito.com',
            scopes: ['email', 'openid'],
            redirectSignIn: ['http://localhost:8080'],
            redirectSignOut: ['http://localhost:8080'],
            responseType: 'code'
          }
        }
      }
    }
  }

  export default awsconfig
